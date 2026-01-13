
/**
 * Get element by ID
 * @param {Document|HTMLElement} parent
 * @param {string} id
 * @returns {HTMLElement|null}
 */
export function getById(parent = document, id) {
  return parent.getElementById(id);
}

/**
 * Get first element matching selector
 * @param {Document|HTMLElement} parent
 * @param {string} selector
 * @returns {Element|null}
 */
export function getOne(parent = document, selector) {
  return parent.querySelector(selector);
}

/**
 * Get all elements matching selector
 * @param {Document|HTMLElement} parent
 * @param {string} selector
 * @returns {NodeListOf<Element>}
 */
export function getAll(parent = document, selector) {
  return parent.querySelectorAll(selector);
}
