import { getAll, getOne } from "../helpers";

export const closeAlert = () => {
    getAll(document, '.alert-component[data-closable="true"]').forEach(function (alert) {
        const closeBtn = getOne(alert, '.alert-close');

        if (!closeBtn) return;

        closeBtn.addEventListener('click', function () {
            alert.remove();
        });
    });
}
