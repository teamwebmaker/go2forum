import { getAll, getOne } from "../helpers";

const dismissedStorageKey = (alertKey) => `dismissed_site_alert:${alertKey}`;

const wasDismissed = (alertKey) => {
    try {
        return localStorage.getItem(dismissedStorageKey(alertKey)) === '1';
    } catch {
        return false;
    }
};

const markDismissed = (alertKey) => {
    try {
        localStorage.setItem(dismissedStorageKey(alertKey), '1');
    } catch {
        // Ignore blocked storage environments (private/incognito restrictions).
    }
};

export const closeAlert = () => {
    getAll(document, '.alert-component[data-closable="true"]').forEach(function (alert) {
        const closeBtn = getOne(alert, '.alert-close');
        const alertKey = (alert.dataset.alertKey ?? alert.getAttribute('data-alert-key') ?? '').trim();
        const hasPersistentDismiss = alert.dataset.persistDismissal === 'true';

        if (alertKey && wasDismissed(alertKey)) {
            alert.remove();

            return;
        }

        if (hasPersistentDismiss) {
            alert.classList.remove('hidden');
        }

        if (!closeBtn) return;

        closeBtn.addEventListener('click', function () {
            if (alertKey) {
                markDismissed(alertKey);
            }

            alert.remove();
        });
    });
}
