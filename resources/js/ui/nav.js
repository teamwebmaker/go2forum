import { getOne } from "../helpers";

export const initMobileNav = () => {
  const menuToggle = getOne(document, "[data-mobile-menu-toggle]");
  const mobileMenu = getOne(document, "[data-mobile-menu]");

  if (!menuToggle || !mobileMenu) return;

  menuToggle.addEventListener("click", () => {
    const isOpen = !mobileMenu.classList.contains("hidden");
    mobileMenu.classList.toggle("hidden", isOpen);
    menuToggle.setAttribute("aria-expanded", String(!isOpen));
  });
};
