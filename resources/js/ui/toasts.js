import { getAll, getOne } from "../helpers";

const TOAST_SELECTOR = "[data-toast]";
const CONTAINER_SELECTOR = "[data-toast-container]";

const APPEAR_STAGGER_MS = 140;
const DISMISS_GAP_MS = 120;

const ENTER_HIDE_CLASSES = [
  "opacity-0",
  "-translate-y-1",
  "scale-[0.98]",
  "pointer-events-none",
];
const EXIT_CLASSES = ["opacity-0", "-translate-y-1", "scale-[0.98]"];

const getContainer = () => getOne(document, CONTAINER_SELECTOR);

const clearTimer = (el, key) => {
  const id = Number(el?.dataset?.[key] || 0);
  if (id) window.clearTimeout(id);
  if (el?.dataset) delete el.dataset[key];
};

export const initToasts = () => {
  if (!document.body || document.body.dataset.toastManagerInit === "1") return;
  document.body.dataset.toastManagerInit = "1";

  // Hover pauses only dismiss timers; show timings continue.
  let isDismissPaused = false;

  const showToast = (toastEl) => {
    if (!toastEl || toastEl.dataset.toastShown === "1") return;

    toastEl.dataset.toastShown = "1";
    toastEl.dataset.toastHidden = "0";

    toastEl.classList.remove(...ENTER_HIDE_CLASSES);
    toastEl.classList.add("pointer-events-auto");
  };

  const setupToast = (toastEl, seq) => {
    if (toastEl.dataset.toastInit === "1") return;
    toastEl.dataset.toastInit = "1";

    if (!toastEl.dataset.toastCreatedAt) {
      toastEl.dataset.toastCreatedAt = String(Date.now());
    }
    toastEl.dataset.toastSeq = String(seq);
    toastEl.dataset.toastTimeout = String(Number(toastEl.dataset.timeout || 1000));

    toastEl.dataset.toastHidden = "1";
    toastEl.dataset.toastShown = "0";
    toastEl.classList.add(...ENTER_HIDE_CLASSES);
  };

  // Snapshot remaining dismiss time so it can resume later.
  const pauseDismissTimers = (toasts) => {
    const now = Date.now();

    toasts.forEach((toast) => {
      const dismissStart = Number(toast.dataset.toastDismissStartAt || 0);
      const dismissDelay = Number(toast.dataset.toastDismissDelay || 0);
      if (dismissStart && dismissDelay) {
        const elapsed = Math.max(0, now - dismissStart);
        const remaining = Math.max(0, dismissDelay - elapsed);
        toast.dataset.toastDismissRemaining = String(remaining);
        clearTimer(toast, "toastDismissTimer");
      }
    });
  };

  // Resume dismiss timers from the stored remaining time.
  const resumeDismissTimers = (toasts) => {
    const now = Date.now();

    toasts.forEach((toast) => {
      const dismissRemaining = Number(toast.dataset.toastDismissRemaining || 0);
      if (dismissRemaining > 0) {
        const dismissTimer = window.setTimeout(() => {
          if (toast.isConnected) dismissToast(toast);
        }, dismissRemaining);

        toast.dataset.toastDismissTimer = String(dismissTimer);
        toast.dataset.toastDismissStartAt = String(now);
        toast.dataset.toastDismissDelay = String(dismissRemaining);
        delete toast.dataset.toastDismissRemaining;
      }
    });
  };

  // Recompute show/dismiss timers based on current DOM order.
  const reschedule = () => {
    const container = getContainer();
    if (!container) return;

    const toasts = Array.from(getAll(container, TOAST_SELECTOR));

    toasts.forEach((toast, i) => setupToast(toast, i));

    // Clear old timers before scheduling new ones.
    toasts.forEach((toast) => {
      clearTimer(toast, "toastShowTimer");
      clearTimer(toast, "toastDismissTimer");
      delete toast.dataset.toastDismissRemaining;
    });

    let cumulativeBefore = 0;

    toasts.forEach((toast, i) => {
      const ownTimeout = Number(toast.dataset.toastTimeout || 1000);
      const showDelay = i * APPEAR_STAGGER_MS;

      const showTimer = window.setTimeout(() => {
        if (toast.isConnected && toast.dataset.toastShown !== "1") {
          showToast(toast);
        }
      }, showDelay);

      toast.dataset.toastShowTimer = String(showTimer);
      toast.dataset.toastShowStartAt = String(Date.now());
      toast.dataset.toastShowDelay = String(showDelay);

      const visibleDuration = cumulativeBefore + ownTimeout;
      const dismissDelay = showDelay + visibleDuration + i * DISMISS_GAP_MS;

      const dismissTimer = window.setTimeout(() => {
        if (toast.isConnected) dismissToast(toast);
      }, dismissDelay);

      toast.dataset.toastDismissTimer = String(dismissTimer);
      toast.dataset.toastDismissStartAt = String(Date.now());
      toast.dataset.toastDismissDelay = String(dismissDelay);

      cumulativeBefore += ownTimeout + DISMISS_GAP_MS;
    });
  };

  const dismissToast = (toastEl) => {
    if (!toastEl || toastEl.dataset.dismissing === "1") return;
    toastEl.dataset.dismissing = "1";

    toastEl.classList.add(...EXIT_CLASSES);
    window.setTimeout(() => {
      toastEl.remove();
    }, 180);
  };

  document.addEventListener("click", (e) => {
    const closeEl = e.target.closest(".js-toast-close");
    if (!closeEl) return;

    const toast = closeEl.closest(TOAST_SELECTOR);
    if (!toast) return;

    e.preventDefault();
    dismissToast(toast);
  });

  reschedule();

  const container = getContainer();
  if (container) {
    container.addEventListener("mouseenter", () => {
      if (isDismissPaused) return;
      isDismissPaused = true;
      const toasts = Array.from(getAll(container, TOAST_SELECTOR));
      pauseDismissTimers(toasts);
    });

    container.addEventListener("mouseleave", () => {
      if (!isDismissPaused) return;
      isDismissPaused = false;
      const toasts = Array.from(getAll(container, TOAST_SELECTOR));
      resumeDismissTimers(toasts);
    });
  }

  // Watch for new toasts injected by server/Livewire/Alpine.
  const observer = new MutationObserver(() => {
    const container = getContainer();
    if (!container) return;
    if (getOne(container, TOAST_SELECTOR)) reschedule();
  });

  observer.observe(document.body, { childList: true, subtree: true });
};
