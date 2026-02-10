/**
 * Escapes a string containing HTML entities.
 * @param {string|number|null|undefined} value - the value to escape
 * @returns {string} - the escaped string
 */
export function escapeHtml(value) {
    if (value === null || value === undefined) return "";
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Format a timestamp value into a human-readable string
 * @param {string|number|null|undefined} value - timestamp value to format
 * @returns {string} - formatted timestamp string, or original value if invalid
 */
export function formatTimestamp(value) {
    if (!value) return "";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    const datePart = date.toLocaleDateString("en-US");
    const timePart = date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });

    return `${datePart}, ${timePart.replace(" ", "")}`;
}

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
