import { getOne } from "../helpers";

export function initAvatarPreview() {
    const input = getOne(document, "[data-avatar-input]");
    const previewWrapper = getOne(document, "[data-avatar-preview]");

    if (!input || !previewWrapper) {
        return;
    }

    const previewImage = getOne(previewWrapper, "[data-avatar-preview-image]");
    const previewName = getOne(previewWrapper, "[data-avatar-preview-name]");

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
