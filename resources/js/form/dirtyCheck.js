// Initializes dirty-check behavior for forms
// - Detects if a user changes any input inside a guarded form
// - Prevents leaving the page when there are unsaved changes
// - Disables submit buttons until something changes
import { getAll } from "../helpers";

export function initDirtyCheck() {
    // Find all forms that should be guarded by dirty-check
    const forms = Array.from(getAll(document, "form[data-dirty-guard]"));

    if (!forms.length) return;

    // Global dirty flag:
    // true  -> user has unsaved changes in ANY guarded form
    // false -> no unsaved changes
    let isDirty = false;

    forms.forEach((form) => {
        // Find submit buttons inside the form that should be controlled
        const submitButtons = Array.from(getAll(form, "[data-dirty-submit]"));

        // Enable/disable submit buttons and update accessibility + styles
        const setSubmitDisabled = (disabled) => {
            submitButtons.forEach((button) => {
                button.disabled = disabled;
                button.classList.toggle("opacity-60", disabled);
                button.setAttribute("aria-disabled", String(disabled));
            });
        };

        // Initially disable submit buttons
        // (nothing has changed yet)
        setSubmitDisabled(true);

        // Marks the form as dirty (unsaved changes exist)
        const markDirty = () => {
            isDirty = true;
            setSubmitDisabled(false);
        };

        // Resets dirty state (used after successful submit)
        const resetDirty = () => {
            isDirty = false;
            setSubmitDisabled(true);
        };

        // Handle text-based inputs as the user types
        form.addEventListener("input", (event) => {
            const target = event.target;

            // Ignore non-input elements
            if (
                !(
                    target instanceof HTMLInputElement ||
                    target instanceof HTMLTextAreaElement
                )
            )
                return;

            // Ignore checkbox, radio, and file inputs here
            // (they fire more reliably on "change" instead)
            if (["checkbox", "radio", "file"].includes(target.type)) return;

            markDirty();
        });

        // Handle inputs that change value discretely
        // (checkboxes, radios, selects, file inputs, etc.)
        form.addEventListener("change", (event) => {
            const target = event.target;

            // Only react to valid form controls
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

        // When the form is submitted, reset dirty state
        form.addEventListener("submit", () => {
            resetDirty();
        });
    });

    // Prevent the user from leaving the page if there are unsaved changes
    window.addEventListener("beforeunload", (event) => {
        if (!isDirty) return;

        // Show confirmation dialog
        event.preventDefault();
        event.returnValue = "";
        return "";
    });
}
