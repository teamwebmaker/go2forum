document.addEventListener("DOMContentLoaded", () => {
    const frame = document.querySelector("[data-document-frame]");
    const title = document.querySelector("[data-modal-heading]");
    const downloadAction = document.querySelector("[data-document-download]");
    const linkAction = document.querySelector("[data-document-link]");
    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";

    const buildPreviewUrl = (rawUrl, hideNativeControls = false) => {
        if (!rawUrl) return rawUrl;
        if (!hideNativeControls) return rawUrl;

        // Native browser PDF viewers may honor these hash flags and hide controls.
        // This is best-effort only and can vary by browser/version.
        const separator = rawUrl.includes("#") ? "&" : "#";
        return `${rawUrl}${separator}toolbar=0&navpanes=0&scrollbar=0`;
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

    document.addEventListener("click", (event) => {
        const trackingTrigger = event.target.closest("[data-document-track-url]");
        if (trackingTrigger) {
            trackRestrictedOpen(
                trackingTrigger.getAttribute("data-document-track-url")
            );
        }

        const trigger = event.target.closest("[data-document-url]");
        if (!trigger) return;

        const url = trigger.getAttribute("data-document-url");
        const name = trigger.getAttribute("data-document-title") || "";
        const hideNativeControls =
            trigger.getAttribute("data-document-hide-native-download") === "1";
        const previewUrl = buildPreviewUrl(url, hideNativeControls);
        const downloadUrl = trigger.getAttribute("data-document-download-url");
        const externalLinkUrl = trigger.getAttribute("data-document-link-url");

        if (frame && previewUrl) frame.src = previewUrl;
        if (title) title.textContent = name || "დოკუმენტი";

        if (downloadAction) {
            if (downloadUrl) {
                downloadAction.setAttribute("href", downloadUrl);
                downloadAction.classList.remove("hidden");
            } else {
                downloadAction.setAttribute("href", "#");
                downloadAction.classList.add("hidden");
            }
        }

        if (linkAction) {
            if (externalLinkUrl) {
                linkAction.setAttribute("href", externalLinkUrl);
                linkAction.classList.remove("hidden");
            } else {
                linkAction.setAttribute("href", "#");
                linkAction.classList.add("hidden");
            }
        }
    });
});
