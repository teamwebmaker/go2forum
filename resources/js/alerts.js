export const closeAlert = () => {
    document.querySelectorAll('.alert-component[data-closable="true"]').forEach(function (alert) {
        var closeBtn = alert.querySelector('.alert-close');

        if (!closeBtn) return;

        closeBtn.addEventListener('click', function () {
            alert.remove();
        });
    });
}