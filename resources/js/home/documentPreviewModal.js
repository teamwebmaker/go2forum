document.addEventListener("DOMContentLoaded", () => {
    const frame = document.querySelector("[data-document-frame]");
    const title = document.querySelector("[data-modal-heading]");

    document.addEventListener("click", (event) => {
        const trigger = event.target.closest("[data-document-url]");
        if (!trigger) return;

        const url = trigger.getAttribute("data-document-url");
        const name = trigger.getAttribute("data-document-title") || "";

        if (frame && url) frame.src = url;
        if (title) title.textContent = name || "დოკუმენტი";
    });
});
