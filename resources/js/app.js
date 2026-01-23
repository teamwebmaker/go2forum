import "./bootstrap";
import "./ui/modal";
import { initDirtyCheck } from "./form/dirtyCheck";
import { initAvatarPreview } from "./profile/avatarPreview";
import { getAll } from "./helpers";

import { closeAlert } from "./ui/alerts";
import { initMobileNav } from "./ui/nav";
import { initToasts } from "./ui/toasts";

document.addEventListener("DOMContentLoaded", () => {
    initToasts();
    closeAlert();
    initMobileNav();
    initDirtyCheck();
    initAvatarPreview();
});

// Disable button on form submit & show loading
document.addEventListener("submit", (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const submitButtons = getAll(form, 'button[type="submit"]');
    submitButtons.forEach((button) => {
        if (button.dataset.no_loading) return;
        button.disabled = true;
        button.classList.add("is_loading");
        button.setAttribute("aria-busy", "true");
    });
});
