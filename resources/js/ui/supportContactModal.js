function copyText(text) {
    if (navigator.clipboard?.writeText) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const textarea = document.createElement("textarea");
        textarea.value = text;
        textarea.setAttribute("readonly", "readonly");
        textarea.style.position = "absolute";
        textarea.style.left = "-9999px";

        document.body.appendChild(textarea);
        textarea.select();

        try {
            const successful = document.execCommand("copy");
            document.body.removeChild(textarea);

            if (!successful) {
                reject(new Error("Copy command failed."));
                return;
            }

            resolve();
        } catch (error) {
            document.body.removeChild(textarea);
            reject(error);
        }
    });
}

function updateCopyButtonState(button, label) {
    const labelNode = button.querySelector("span.whitespace-nowrap");

    if (labelNode) {
        labelNode.textContent = label;
        return;
    }

    button.textContent = label;
}

export function initSupportContactModal() {
    document.addEventListener("click", async (event) => {
        const copyButton = event.target.closest("[data-support-copy-email]");

        if (copyButton) {
            const email = (copyButton.getAttribute("data-support-email") || "").trim();

            if (email === "") {
                return;
            }

            const defaultLabel = copyButton.getAttribute("data-default-label") || "Copy";
            const copiedLabel = copyButton.getAttribute("data-copied-label") || "Copied";

            try {
                await copyText(email);
                updateCopyButtonState(copyButton, copiedLabel);

                window.setTimeout(() => {
                    updateCopyButtonState(copyButton, defaultLabel);
                }, 1800);
            } catch {
                updateCopyButtonState(copyButton, defaultLabel);
            }

            return;
        }

        const continueButton = event.target.closest("[data-support-open-gmail]");

        if (!continueButton) {
            return;
        }

        const url = (continueButton.getAttribute("data-support-gmail-url") || "").trim();

        if (url === "") {
            return;
        }

        const modal = continueButton.closest("[data-modal]");

        if (modal?.id) {
            window.UIModal?.close(modal.id);
        }

        window.location.assign(url);
    });
}
