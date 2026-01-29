import { getAll, getOne } from "../helpers";

// Safely escape input name for querySelector
const escapeName = (name) => {
    if (window.CSS?.escape) return window.CSS.escape(name);
    return name.replace(/"/g, '\\"');
};

// Get trimmed search value from URL (?search=...)
const getSearchTerm = (paramName) => {
    const params = new URLSearchParams(window.location.search);
    return params.get(paramName)?.trim() || "";
};

// Stable stringify of URLSearchParams to detect no-op submissions
const paramsKey = (params) =>
    [...params.entries()]
        .sort(([aKey, aVal], [bKey, bVal]) =>
            aKey === bKey ? aVal.localeCompare(bVal) : aKey.localeCompare(bKey),
        )
        .map(([k, v]) => `${k}=${v}`)
        .join("&");

export function initSearchBars() {
    getAll(document, "[data-search-bar]").forEach((form) => {
        // Prevent double init
        if (form.dataset.searchInit) return;
        form.dataset.searchInit = "true";

        const searchName = form.dataset.searchName;
        if (!searchName) return;

        const input = getOne(form, `input[name="${escapeName(searchName)}"]`);
        if (!input) return;

        // Check if search exists in URL
        const isSearchApplied = () => getSearchTerm(searchName).length > 0;

        // Clear button → empty input + clear URL
        getAll(form, ".clear-search").forEach((btn) => {
            btn.addEventListener("click", () => {
                if (!input.value.trim()) return; // Avoid extra submit if already empty
                input.value = "";
                form.submit();
            });
        });

        // Auto-clear URL when input becomes empty
        input.addEventListener("input", () => {
            if (!input.value.trim() && isSearchApplied()) {
                form.submit();
            }
        });

        // Block redundant submits when nothing changed (same search term & params)
        form.addEventListener("submit", (event) => {
            const currentKey = paramsKey(new URLSearchParams(window.location.search));
            const nextKey = paramsKey(new URLSearchParams(new FormData(form)));
            if (currentKey === nextKey) {
                event.preventDefault();
            }
        });

        // Auto-focus input when search param exists in URL
        const currentSearchTerm = getSearchTerm(searchName);
        if (currentSearchTerm && input.value.trim() === currentSearchTerm) {
            setTimeout(() => {
                input.focus();

                // move cursor to end
                const val = input.value;
                input.value = "";
                input.value = val;
            }, 100);
        }
    });
}
