const HIDDEN_GO_DOWN_CLASSES = ['opacity-0', 'translate-y-2', 'pointer-events-none'];
const VISIBLE_GO_DOWN_CLASSES = ['opacity-100', 'translate-y-0'];

const normalizeConfig = (config) => ({
    namespace: config.namespace,
    rootSelector: config.rootSelector,
    eventName: config.eventName,
    listSelector: config.listSelector ?? '[data-chat-list]',
    goDownSelector: config.goDownSelector ?? '[data-chat-go-down]',
    scrollThreshold: config.scrollThreshold ?? 120,
    bottomThreshold: config.bottomThreshold ?? 120,
    hasMoreDatasetKey: config.hasMoreDatasetKey ?? 'hasMore',
    loadMethod: config.loadMethod ?? 'loadOlder',
    preserveTtlMs: config.preserveTtlMs ?? 2500,
});

export const initLivewireChatScroll = (config) => {
    const options = normalizeConfig(config);
    if (!options.namespace || !options.rootSelector || !options.eventName) {
        return;
    }

    window.__livewireChatScrollInit = window.__livewireChatScrollInit ?? new Set();
    if (window.__livewireChatScrollInit.has(options.namespace)) {
        return;
    }
    window.__livewireChatScrollInit.add(options.namespace);

    let scrollTicking = false;
    const rootObservers = new WeakMap();
    const pendingPreserve = new Map();
    let preserveRequestId = 0;

    const clearPendingPreserve = (componentId) => {
        const state = pendingPreserve.get(componentId);
        if (!state) return;

        if (state.timeoutId) {
            window.clearTimeout(state.timeoutId);
        }

        pendingPreserve.delete(componentId);
    };

    const clearAllPendingPreserve = () => {
        pendingPreserve.forEach((state) => {
            if (state?.timeoutId) {
                window.clearTimeout(state.timeoutId);
            }
        });
        pendingPreserve.clear();
    };

    const getRoot = (list) => list?.closest?.(options.rootSelector);

    const updateGoDown = (list) => {
        const root = getRoot(list);
        if (!root) return;

        const button = root.querySelector(options.goDownSelector);
        if (!button) return;

        const distanceFromBottom = list.scrollHeight - (list.scrollTop + list.clientHeight);
        const canScroll = list.scrollHeight > list.clientHeight + 4;
        const shouldShow = canScroll && distanceFromBottom > options.bottomThreshold;

        if (shouldShow) {
            button.classList.remove(...HIDDEN_GO_DOWN_CLASSES);
            button.classList.add(...VISIBLE_GO_DOWN_CLASSES);
            return;
        }

        button.classList.add(...HIDDEN_GO_DOWN_CLASSES);
        button.classList.remove(...VISIBLE_GO_DOWN_CLASSES);
    };

    const captureScrollState = (list) => {
        const root = getRoot(list);
        if (!root) return null;

        const componentId = root.dataset.componentId || root.getAttribute('wire:id');
        if (!componentId) return null;

        return {
            componentId,
            top: list.scrollTop,
            height: list.scrollHeight,
        };
    };

    const applyPreservedScroll = (list, state) => {
        const newHeight = list.scrollHeight;
        list.scrollTop = state.top + (newHeight - state.height);
        updateGoDown(list);
    };

    const ensureRootObserver = (root) => {
        if (!root || rootObservers.has(root)) return;

        const observer = new MutationObserver(() => {
            const componentId = root.dataset.componentId || root.getAttribute('wire:id');
            if (!componentId) return;

            const state = pendingPreserve.get(componentId);
            if (!state) return;
            if (state.expiresAt <= Date.now()) {
                clearPendingPreserve(componentId);
                return;
            }

            const list = root.querySelector(options.listSelector);
            if (!list) return;

            applyPreservedScroll(list, state);
            clearPendingPreserve(componentId);
        });

        observer.observe(root, { childList: true, subtree: true });
        rootObservers.set(root, observer);
    };

    const loadOlder = async (list) => {
        const root = getRoot(list);
        if (!root) return;

        if (root.dataset[options.hasMoreDatasetKey] !== '1') return;
        if (list.dataset.loadingOlder === '1') return;
        if (list.scrollTop > options.scrollThreshold) return;

        const componentId = root.dataset.componentId || root.getAttribute('wire:id');
        if (!componentId || !window.Livewire?.find) return;

        const component = window.Livewire.find(componentId);
        if (!component) return;

        list.dataset.loadingOlder = '1';
        const previousHeight = list.scrollHeight;
        const previousTop = list.scrollTop;

        try {
            await component.call(options.loadMethod);
        } finally {
            requestAnimationFrame(() => {
                const newHeight = list.scrollHeight;
                list.scrollTop = previousTop + (newHeight - previousHeight);
                list.dataset.loadingOlder = '0';
                updateGoDown(list);
            });
        }
    };

    const onScroll = (event) => {
        const target = event.target;
        if (!target || !target.matches?.(options.listSelector)) return;
        if (!target.closest(options.rootSelector)) return;
        if (scrollTicking) return;

        scrollTicking = true;
        requestAnimationFrame(() => {
            scrollTicking = false;
            updateGoDown(target);
            loadOlder(target);
        });
    };

    const refreshAll = () => {
        document.querySelectorAll(options.rootSelector).forEach((root) => {
            ensureRootObserver(root);
        });

        document.querySelectorAll(`${options.rootSelector} ${options.listSelector}`).forEach((list) => {
            updateGoDown(list);
        });
    };

    const onEditSaveClick = (event) => {
        const target = event.target;
        const trigger = target?.closest?.('[wire\\:click*="saveEditedMessage"]');
        if (!trigger) return;

        const root = trigger.closest(options.rootSelector);
        if (!root) return;

        const list = root.querySelector(options.listSelector);
        if (!list) return;

        const state = captureScrollState(list);
        if (!state) return;

        const requestId = ++preserveRequestId;
        const existing = pendingPreserve.get(state.componentId);
        if (existing?.timeoutId) {
            window.clearTimeout(existing.timeoutId);
        }

        const pendingState = {
            ...state,
            requestId,
            expiresAt: Date.now() + options.preserveTtlMs,
        };

        pendingState.timeoutId = window.setTimeout(() => {
            const latestState = pendingPreserve.get(state.componentId);
            if (!latestState || latestState.requestId !== requestId) return;
            clearPendingPreserve(state.componentId);
        }, options.preserveTtlMs);

        ensureRootObserver(root);
        pendingPreserve.set(state.componentId, pendingState);
    };

    document.addEventListener(options.eventName, (event) => {
        const id = event?.detail?.id;
        if (!id) return;

        const root = document.querySelector(
            `${options.rootSelector}[data-component-id="${id}"]`
        );
        if (!root) return;

        const list = root.querySelector(options.listSelector);
        if (!list) return;

        list.scrollTop = list.scrollHeight;
        updateGoDown(list);
        clearPendingPreserve(id);
    });

    document.addEventListener('click', onEditSaveClick, true);
    document.addEventListener('scroll', onScroll, true);
    document.addEventListener('livewire:navigated', () => {
        clearAllPendingPreserve();
        refreshAll();
    });
    document.addEventListener('livewire:load', refreshAll);
    window.addEventListener('load', refreshAll);
};
