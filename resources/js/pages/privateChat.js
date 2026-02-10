import { initLivewireChatScroll } from "./shared/livewireChatScroll";

initLivewireChatScroll({
    namespace: "private-chat",
    rootSelector: "[data-private-chat-livewire]",
    eventName: "private-chat-scroll-bottom",
});

