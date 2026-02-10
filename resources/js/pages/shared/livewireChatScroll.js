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
        document.querySelectorAll(`${options.rootSelector} ${options.listSelector}`).forEach((list) => {
            updateGoDown(list);
        });
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
    });

    document.addEventListener('scroll', onScroll, true);
    document.addEventListener('livewire:navigated', refreshAll);
    document.addEventListener('livewire:load', refreshAll);
    window.addEventListener('load', refreshAll);
};

