import { initLivewireChatScroll } from "./shared/livewireChatScroll";

initLivewireChatScroll({
    namespace: "topic-chat",
    rootSelector: "[data-topic-chat-livewire]",
    eventName: "topic-chat-scroll-bottom",
});
