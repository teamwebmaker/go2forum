import * as pdfjs from "pdfjs-dist/legacy/build/pdf.mjs";
import pdfWorkerUrl from "pdfjs-dist/legacy/build/pdf.worker.min.mjs?url";

pdfjs.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const MODAL_ID = "document-viewer";
const MIN_RENDER_WIDTH = 240;
const MIN_MOBILE_RENDER_WIDTH = 520;
const MAX_DESKTOP_RENDER_WIDTH = 780;
const MOBILE_BREAKPOINT = 640;
const MIN_VIEWER_ZOOM = 1;
const MAX_VIEWER_ZOOM = 2.5;
const MAX_DEVICE_PIXEL_RATIO = 3;
const MAX_CANVAS_PIXELS = 8_000_000;
const DEFAULT_PAGE_ASPECT_RATIO = 0.707;
const PAGE_PRELOAD_MARGIN = "700px 0px 900px";
const RESIZE_DEBOUNCE_MS = 180;

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById(MODAL_ID);
    const title = document.querySelector("[data-modal-heading]");
    const loading = document.querySelector("[data-document-loading]");
    const error = document.querySelector("[data-document-error]");
    const pages = document.querySelector("[data-document-pages]");
    const scrollRegion = document.querySelector("[data-document-scroll-region]");
    const downloadAction = document.querySelector("[data-document-download]");
    const linkAction = document.querySelector("[data-document-link]");
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";

    if (
        !modal ||
        !title ||
        !loading ||
        !error ||
        !pages ||
        !scrollRegion
    ) {
        return;
    }

    let activeDocument = null;
    let activePdf = null;
    let activeLoadingTask = null;
    let renderToken = 0;
    let resizeTimer = null;
    let lastRenderedWidth = 0;
    let pageObserver = null;
    let activePageStates = null;
    let viewerZoom = 1;
    let pinchState = null;

    const isModalOpen = () =>
        !modal.classList.contains("hidden") &&
        modal.getAttribute("aria-hidden") !== "true";

    const isViewerGesture = (event) =>
        isModalOpen() &&
        event.target instanceof Node &&
        modal.contains(event.target);

    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    const getTouchDistance = (touches) => {
        const first = touches[0];
        const second = touches[1];

        if (!first || !second) return 0;

        return Math.hypot(
            second.clientX - first.clientX,
            second.clientY - first.clientY,
        );
    };

    const getTouchCenter = (touches) => {
        const first = touches[0];
        const second = touches[1];

        if (!first || !second) return null;

        return {
            x: (first.clientX + second.clientX) / 2,
            y: (first.clientY + second.clientY) / 2,
        };
    };

    const getFallbackZoomCenter = () => {
        const rect = scrollRegion.getBoundingClientRect();

        return {
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2,
        };
    };

    const getZoomAnchor = (center) => {
        const scrollRect = scrollRegion.getBoundingClientRect();
        const target = document.elementFromPoint(center.x, center.y);
        const stage = target?.closest?.("[data-page-stage]");
        const stageRect = stage?.getBoundingClientRect();

        return {
            center,
            contentY: scrollRegion.scrollTop + center.y - scrollRect.top,
            stage: stage || null,
            stageContentX:
                stage && stageRect
                    ? stage.scrollLeft + center.x - stageRect.left
                    : null,
        };
    };

    const restoreZoomAnchor = (anchor, scaleRatio) => {
        if (!anchor) return;

        const scrollRect = scrollRegion.getBoundingClientRect();
        scrollRegion.scrollTop =
            anchor.contentY * scaleRatio - (anchor.center.y - scrollRect.top);

        if (!anchor.stage || anchor.stageContentX === null) return;

        const stageRect = anchor.stage.getBoundingClientRect();
        anchor.stage.scrollLeft =
            anchor.stageContentX * scaleRatio -
            (anchor.center.x - stageRect.left);
    };

    const applyLiveZoomWidth = () => {
        const targetWidth = getZoomedRenderWidth();

        activePageStates?.forEach((state) => {
            state.targetWidth = targetWidth;
        });

        pages.querySelectorAll("canvas").forEach((canvas) => {
            const heightRatio = Number(canvas.dataset.pageHeightRatio);

            canvas.style.width = `${targetWidth}px`;

            if (Number.isFinite(heightRatio) && heightRatio > 0) {
                canvas.style.height = `${targetWidth * heightRatio}px`;
            }
        });

        pages
            .querySelectorAll("[data-page-placeholder]")
            .forEach((placeholder) => {
                placeholder.style.width = `${targetWidth}px`;
            });
    };

    const setViewerZoom = (nextZoom, center = null) => {
        const previousZoom = viewerZoom;
        const zoom = clamp(nextZoom, MIN_VIEWER_ZOOM, MAX_VIEWER_ZOOM);

        if (Math.abs(zoom - previousZoom) < 0.01) return;

        const anchor = getZoomAnchor(center || getFallbackZoomCenter());
        viewerZoom = zoom;
        applyLiveZoomWidth();
        restoreZoomAnchor(anchor, viewerZoom / previousZoom);
    };

    const startGesturePinchZoom = (event) => {
        if (!isViewerGesture(event)) return;

        event.preventDefault();
        pinchState = {
            type: "gesture",
            distance: 0,
            zoom: viewerZoom,
            center: {
                x: event.clientX || getFallbackZoomCenter().x,
                y: event.clientY || getFallbackZoomCenter().y,
            },
        };
    };

    const updateGesturePinchZoom = (event) => {
        if (!pinchState || !isViewerGesture(event)) return;

        event.preventDefault();
        setViewerZoom(
            pinchState.zoom * (event.scale || 1),
            event.clientX && event.clientY
                ? { x: event.clientX, y: event.clientY }
                : pinchState.center,
        );
    };

    const endGesturePinchZoom = (event) => {
        if (!pinchState || !isViewerGesture(event)) return;

        event.preventDefault();
        pinchState = null;
    };

    const startViewerPinchZoom = (event) => {
        if (!isViewerGesture(event) || (event.touches?.length || 0) < 2) {
            return;
        }

        event.preventDefault();
        pinchState = {
            type: "touch",
            distance: getTouchDistance(event.touches),
            zoom: viewerZoom,
            center: getTouchCenter(event.touches),
        };
    };

    const updateViewerPinchZoom = (event) => {
        if (
            !pinchState ||
            !isViewerGesture(event) ||
            (event.touches?.length || 0) < 2
        ) {
            return;
        }

        if (pinchState.type === "gesture") {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        const nextDistance = getTouchDistance(event.touches);
        if (!pinchState.distance || !nextDistance) return;

        setViewerZoom(
            pinchState.zoom * (nextDistance / pinchState.distance),
            getTouchCenter(event.touches) || pinchState.center,
        );
    };

    const endViewerPinchZoom = (event) => {
        if (!pinchState || (event.touches?.length || 0) >= 2) {
            return;
        }

        event.preventDefault();
        pinchState = null;
    };

    modal.style.touchAction = "pan-x pan-y";
    scrollRegion.style.touchAction = "pan-x pan-y";
    pages.style.touchAction = "pan-x pan-y";

    modal.addEventListener("gesturestart", startGesturePinchZoom, {
        passive: false,
    });
    modal.addEventListener("gesturechange", updateGesturePinchZoom, {
        passive: false,
    });
    modal.addEventListener("gestureend", endGesturePinchZoom, {
        passive: false,
    });
    modal.addEventListener("touchstart", startViewerPinchZoom, {
        passive: false,
    });
    modal.addEventListener("touchmove", updateViewerPinchZoom, {
        passive: false,
    });
    modal.addEventListener("touchend", endViewerPinchZoom, {
        passive: false,
    });
    modal.addEventListener("touchcancel", endViewerPinchZoom, {
        passive: false,
    });

    const setLoading = (isLoading) => {
        loading.classList.toggle("hidden", !isLoading);
    };

    const setError = (message = "") => {
        if (!message) {
            error.classList.add("hidden");
            error.textContent =
                "დოკუმენტის ჩატვირთვა ვერ მოხერხდა. სცადეთ ხელახლა ან გახსენით ბმულით.";
            return;
        }

        error.textContent = message;
        error.classList.remove("hidden");
    };

    const disconnectPageObserver = () => {
        if (!pageObserver) return;

        pageObserver.disconnect();
        pageObserver = null;
    };

    const resetPages = (resetScroll = true) => {
        disconnectPageObserver();
        activePageStates = null;
        pages.innerHTML = "";
        pages.classList.add("hidden");
        loading.classList.remove("hidden");

        if (resetScroll) {
            scrollRegion.scrollTop = 0;
        }
    };

    const clearActiveDocument = () => {
        renderToken += 1;
        activeDocument = null;
        lastRenderedWidth = 0;
        activePageStates = null;
        viewerZoom = 1;
        pinchState = null;
        disconnectPageObserver();

        if (activePdf) {
            activePdf.destroy().catch(() => {
                // Ignore teardown errors while closing the viewer.
            });
            activePdf = null;
        }

        if (activeLoadingTask) {
            activeLoadingTask.destroy().catch(() => {
                // Ignore teardown errors while switching documents.
            });
            activeLoadingTask = null;
        }
    };

    const updateAction = (action, url) => {
        if (!action) return;

        if (url) {
            action.setAttribute("href", url);
            action.classList.remove("hidden");
            return;
        }

        action.setAttribute("href", "#");
        action.classList.add("hidden");
    };

    const updateLinkAction = (action, url, label = "ბმულის გახსნა") => {
        if (!action) return;

        action.textContent = label;
        updateAction(action, url);
    };

    const trackRestrictedOpen = (trackUrl) => {
        if (!trackUrl) return;

        fetch(trackUrl, {
            method: "POST",
            credentials: "same-origin",
            keepalive: true,
            cache: "no-store",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        }).catch(() => {
            // Best-effort analytics call; do not block UI on failure.
        });
    };

    const waitForNextPaint = () =>
        new Promise((resolve) => {
            requestAnimationFrame(() => {
                requestAnimationFrame(resolve);
            });
        });

    const createPageShell = (pageNumber, targetWidth, pageAspectRatio) => {
        const card = document.createElement("article");
        card.className =
            "rounded-lg border border-slate-200 bg-white p-1.5 shadow-sm sm:rounded-2xl sm:p-4";
        card.dataset.pageNumber = String(pageNumber);

        const label = document.createElement("div");
        label.className =
            "mb-1.5 flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-slate-400 sm:mb-3";
        label.textContent = `გვერდი ${pageNumber}`;

        const stage = document.createElement("div");
        stage.className =
            "overflow-x-auto overflow-y-hidden rounded-md border border-slate-200 bg-slate-50 sm:rounded-xl";
        stage.dataset.pageStage = "true";

        const placeholder = document.createElement("div");
        placeholder.className =
            "mx-auto flex min-h-[16rem] flex-col items-center justify-center gap-2 bg-white/70 text-slate-500";
        placeholder.dataset.pagePlaceholder = "true";
        placeholder.style.width = `${targetWidth}px`;
        placeholder.style.maxWidth = "none";
        placeholder.style.aspectRatio = String(pageAspectRatio);

        const spinner = document.createElement("div");
        spinner.className =
            "h-6 w-6 animate-spin rounded-full border-2 border-slate-200 border-t-slate-500";

        const loadingLabel = document.createElement("span");
        loadingLabel.className = "text-xs font-medium";
        loadingLabel.textContent = "გვერდი იტვირთება";

        placeholder.append(spinner, loadingLabel);
        stage.append(placeholder);
        card.append(label, stage);

        return { card, stage };
    };

    const getBaseRenderWidth = () => {
        if (window.innerWidth < MOBILE_BREAKPOINT) {
            const mobileWidth = scrollRegion.clientWidth - 8;

            return Math.max(mobileWidth, MIN_MOBILE_RENDER_WIDTH);
        }

        const desktopWidth = scrollRegion.clientWidth - 72;
        return Math.max(
            MIN_RENDER_WIDTH,
            Math.min(desktopWidth, MAX_DESKTOP_RENDER_WIDTH),
        );
    };

    const getRenderWidth = () => getBaseRenderWidth();

    const getZoomedRenderWidth = () =>
        Math.round(getBaseRenderWidth() * viewerZoom);

    const getPixelRatio = (viewport) => {
        const devicePixelRatio = Math.min(
            window.devicePixelRatio || 1,
            MAX_DEVICE_PIXEL_RATIO,
        );
        const viewportPixels = viewport.width * viewport.height;
        const memorySafeRatio = Math.sqrt(MAX_CANVAS_PIXELS / viewportPixels);

        return Math.max(1, Math.min(devicePixelRatio, memorySafeRatio));
    };

    const getDocumentAspectRatio = async (pdf, currentToken) => {
        if (!pdf.numPages) return DEFAULT_PAGE_ASPECT_RATIO;

        const page = await pdf.getPage(1);
        const viewport = page.getViewport({ scale: 1 });
        const aspectRatio = viewport.width / viewport.height;
        page.cleanup();

        if (currentToken !== renderToken || !isModalOpen()) {
            return DEFAULT_PAGE_ASPECT_RATIO;
        }

        return aspectRatio || DEFAULT_PAGE_ASPECT_RATIO;
    };

    const renderPage = async (
        pdf,
        pageNumber,
        shell,
        targetWidth,
        currentToken,
    ) => {
        if (currentToken !== renderToken || !isModalOpen()) {
            return false;
        }

        const page = await pdf.getPage(pageNumber);

        try {
            if (currentToken !== renderToken || !isModalOpen()) {
                return false;
            }

            const baseViewport = page.getViewport({ scale: 1 });
            const scale = targetWidth / baseViewport.width;
            const viewport = page.getViewport({ scale });
            const pixelRatio = getPixelRatio(viewport);
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d", {
                alpha: false,
                willReadFrequently: false,
            });

            if (!context) {
                throw new Error("Canvas context unavailable.");
            }

            canvas.className = "mx-auto block bg-white";
            canvas.dataset.pageHeightRatio = String(
                viewport.height / viewport.width,
            );
            canvas.width = Math.floor(viewport.width * pixelRatio);
            canvas.height = Math.floor(viewport.height * pixelRatio);
            canvas.style.width = `${viewport.width}px`;
            canvas.style.height = `${viewport.height}px`;

            const renderTask = page.render({
                canvasContext: context,
                viewport,
                transform:
                    pixelRatio === 1
                        ? null
                        : [pixelRatio, 0, 0, pixelRatio, 0, 0],
            });

            await renderTask.promise;

            if (currentToken !== renderToken || !isModalOpen()) {
                return false;
            }

            shell.stage.replaceChildren(canvas);
            return true;
        } finally {
            page.cleanup();
        }
    };

    const renderLazyPage = (state, currentToken) => {
        if (!state) return Promise.resolve(false);
        if (state.rendered || state.rendering) return state.promise;

        state.rendering = true;
        state.promise = renderPage(
            state.pdf,
            state.pageNumber,
            state.shell,
            state.targetWidth,
            currentToken,
        )
            .then((rendered) => {
                if (rendered && currentToken === renderToken) {
                    state.rendered = true;
                }

                return rendered;
            })
            .catch((renderError) => {
                if (currentToken !== renderToken) {
                    return;
                }

                state.shell.stage.replaceChildren();
                setError('დოკუმენტის გვერდის ჩატვირთვა ვერ მოხერხდა.');
                // eslint-disable-next-line no-console
                console.error(renderError);
            })
            .finally(() => {
                state.rendering = false;
            });

        return state.promise;
    };

    const observeLazyPages = (pageStates, currentToken) => {
        disconnectPageObserver();

        if (!window.IntersectionObserver) {
            (async () => {
                for (const state of pageStates.values()) {
                    if (state.pageNumber === 1) continue;
                    await renderLazyPage(state, currentToken);
                }
            })();
            return;
        }

        pageObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (
                        !entry.isIntersecting ||
                        currentToken !== renderToken ||
                        !isModalOpen()
                    ) {
                        return;
                    }

                    const pageNumber = Number(
                        entry.target.dataset.pageNumber,
                    );
                    const state = pageStates.get(pageNumber);

                    if (!state) return;

                    renderLazyPage(state, currentToken);

                    if (state.rendered || state.rendering) {
                        pageObserver?.unobserve(entry.target);
                    }
                });
            },
            {
                root: scrollRegion,
                rootMargin: PAGE_PRELOAD_MARGIN,
                threshold: 0,
            },
        );

        pageStates.forEach((state) => {
            if (state.pageNumber === 1) return;
            pageObserver.observe(state.shell.card);
        });
    };

    const renderLoadedDocument = async (pdf, options = {}) => {
        const { preserveScroll = false } = options;
        const currentToken = ++renderToken;
        const targetWidth = getRenderWidth();
        const zoomedTargetWidth = getZoomedRenderWidth();
        const pageAspectRatio = await getDocumentAspectRatio(pdf, currentToken);
        const fragment = document.createDocumentFragment();
        const pageStates = new Map();
        const maxScrollTop = Math.max(
            scrollRegion.scrollHeight - scrollRegion.clientHeight,
            1,
        );
        const previousScrollRatio = preserveScroll
            ? scrollRegion.scrollTop / maxScrollTop
            : 0;

        lastRenderedWidth = targetWidth;
        resetPages(!preserveScroll);
        setError("");
        setLoading(true);

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
            if (currentToken !== renderToken || !isModalOpen()) {
                return;
            }

            const shell = createPageShell(
                pageNumber,
                zoomedTargetWidth,
                pageAspectRatio,
            );
            pageStates.set(pageNumber, {
                pdf,
                pageNumber,
                shell,
                targetWidth: zoomedTargetWidth,
                rendered: false,
                rendering: false,
                promise: null,
            });
            fragment.append(shell.card);
        }

        if (currentToken !== renderToken || !isModalOpen()) {
            return;
        }

        pages.replaceChildren(fragment);
        activePageStates = pageStates;
        pages.classList.remove("hidden");
        if (preserveScroll) {
            const nextMaxScrollTop = Math.max(
                scrollRegion.scrollHeight - scrollRegion.clientHeight,
                0,
            );
            scrollRegion.scrollTop = nextMaxScrollTop * previousScrollRatio;
        }
        setLoading(false);
        observeLazyPages(pageStates, currentToken);

        await waitForNextPaint();
        await renderLazyPage(pageStates.get(1), currentToken);

        if (currentToken !== renderToken || !isModalOpen()) {
            return;
        }
    };

    const loadDocument = async (documentConfig) => {
        const { url, name } = documentConfig;

        if (!url) return;

        clearActiveDocument();
        activeDocument = documentConfig;
        title.textContent = name || "დოკუმენტი";
        resetPages();
        setError("");
        setLoading(true);

        try {
            const loadingTask = pdfjs.getDocument({
                url,
                withCredentials: true,
            });

            activeLoadingTask = loadingTask;
            const pdf = await loadingTask.promise;

            if (!activeDocument || activeDocument.url !== url) {
                pdf.destroy().catch(() => {
                    // Ignore teardown errors for superseded documents.
                });
                return;
            }

            activeLoadingTask = null;
            activePdf = pdf;
            await renderLoadedDocument(pdf);
        } catch (loadError) {
            if (!activeDocument || activeDocument.url !== url) {
                return;
            }

            activeLoadingTask = null;
            activePdf = null;
            resetPages();
            setLoading(false);
            setError(
                'დოკუმენტის გახსნა ვერ მოხერხდა. სცადეთ ჩამოტვირთვა.',
            );
            // eslint-disable-next-line no-console
            console.error(loadError);
        }
    };

    const queueResizeRender = () => {
        if (!activePdf || !activeDocument || !isModalOpen()) {
            return;
        }

        const nextWidth = getRenderWidth();
        if (Math.abs(nextWidth - lastRenderedWidth) < 24) {
            return;
        }

        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
            if (!activePdf || !activeDocument || !isModalOpen()) {
                return;
            }

            renderLoadedDocument(activePdf, { preserveScroll: true }).catch((renderError) => {
                if (!activeDocument || !isModalOpen()) {
                    return;
                }

                setLoading(false);
                setError(
                    'დოკუმენტის ზომაზე მორგება ვერ მოხერხდა.',
                );
                // eslint-disable-next-line no-console
                console.error(renderError);
            });
        }, RESIZE_DEBOUNCE_MS);
    };

    document.addEventListener("click", (event) => {
        const trackingTrigger = event.target.closest("[data-document-track-url]");
        if (trackingTrigger) {
            trackRestrictedOpen(
                trackingTrigger.getAttribute("data-document-track-url"),
            );
        }

        const trigger = event.target.closest("[data-document-url]");
        if (!trigger) return;

        const url = trigger.getAttribute("data-document-url");
        const name = trigger.getAttribute("data-document-title") || "დოკუმენტი";
        const downloadUrl = trigger.getAttribute("data-document-download-url");
        const externalLinkUrl = trigger.getAttribute("data-document-link-url");
        const requiresAuth =
            trigger.getAttribute("data-document-requires-auth") === "1";
        const linkUrl = externalLinkUrl || (requiresAuth ? null : url);
        const linkLabel = externalLinkUrl ? "ბმულის გახსნა" : "ცალკე გახსნა";

        updateAction(downloadAction, downloadUrl);
        updateLinkAction(linkAction, linkUrl, linkLabel);
        loadDocument({
            url,
            name,
        });
    });

    modal.addEventListener("ui-modal:closed", () => {
        window.clearTimeout(resizeTimer);
        clearActiveDocument();
        resetPages();
        setError("");
        setLoading(false);
        title.textContent = "---";
    });

    if (window.ResizeObserver) {
        const observer = new ResizeObserver(() => {
            queueResizeRender();
        });

        observer.observe(scrollRegion);
    } else {
        window.addEventListener("resize", queueResizeRender);
    }

    window.addEventListener("orientationchange", queueResizeRender);
});
