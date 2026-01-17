import "./bootstrap";
import './modal';

import { initToasts } from "./toasts";
import { closeAlert } from "./alerts";
document.addEventListener("DOMContentLoaded", () => {
  initToasts();
  closeAlert();
});

// Disable button on form submit
document.addEventListener("submit", (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const submitButtons = form.querySelectorAll('button[type="submit"]');
  submitButtons.forEach((button) => {
    if (button.dataset.no_loading) return;
    button.disabled = true;
    button.classList.add("is_loading");
    button.setAttribute("aria-busy", "true");
  });
});
