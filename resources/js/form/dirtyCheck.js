export function initDirtyCheck() {
    const forms = Array.from(
        document.querySelectorAll("form[data-dirty-guard]"),
    );

    if (!forms.length) return;

    let isDirty = false;

    forms.forEach((form) => {
        const submitButtons = Array.from(
            form.querySelectorAll("[data-dirty-submit]"),
        );

        const setSubmitDisabled = (disabled) => {
            submitButtons.forEach((button) => {
                button.disabled = disabled;
                button.classList.toggle("opacity-60", disabled);
                button.setAttribute("aria-disabled", String(disabled));
            });
        };

        // Start disabled until a change is made
        setSubmitDisabled(true);

        const markDirty = () => {
            isDirty = true;
            setSubmitDisabled(false);
        };

        const resetDirty = () => {
            isDirty = false;
            setSubmitDisabled(true);
        };

        form.addEventListener("input", (event) => {
            const target = event.target;
            if (
                !(
                    target instanceof HTMLInputElement ||
                    target instanceof HTMLTextAreaElement
                )
            )
                return;
            if (["checkbox", "radio", "file"].includes(target.type)) return; // handled in change
            markDirty();
        });

        form.addEventListener("change", (event) => {
            const target = event.target;
            if (
                !(
                    target instanceof HTMLInputElement ||
                    target instanceof HTMLSelectElement ||
                    target instanceof HTMLTextAreaElement
                )
            )
                return;
            markDirty();
        });

        form.addEventListener("submit", () => {
            resetDirty();
        });
    });

    window.addEventListener("beforeunload", (event) => {
        if (!isDirty) return;
        event.preventDefault();
        event.returnValue = "";
        return "";
    });
}
