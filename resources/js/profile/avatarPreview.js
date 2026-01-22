export function initAvatarPreview() {
    const input = document.querySelector("[data-avatar-input]");
    const previewWrapper = document.querySelector("[data-avatar-preview]");

    if (!input || !previewWrapper) {
        return;
    }

    const previewImage = previewWrapper.querySelector("[data-avatar-preview-image]");
    const previewName = previewWrapper.querySelector("[data-avatar-preview-name]");

    input.addEventListener("change", () => {
        const file = input.files?.[0];

        if (!file) {
            previewWrapper.classList.add("hidden");
            return;
        }

        if (previewName) {
            previewName.textContent = file.name;
        }

        if (previewImage) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const result = event.target?.result;
                if (typeof result === "string") {
                    previewImage.src = result;
                }
            };
            reader.readAsDataURL(file);
        }

        previewWrapper.classList.remove("hidden");
    });
}
