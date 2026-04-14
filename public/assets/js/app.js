document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (event) => {
            const message = element.getAttribute('data-confirm') || 'Подтвердить действие?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const autofocusElement = document.querySelector('[data-autofocus]');
    if (autofocusElement instanceof HTMLElement) {
        window.setTimeout(() => autofocusElement.focus(), 60);
    }

    document.querySelectorAll('.alert').forEach((alertElement) => {
        window.setTimeout(() => {
            if (window.bootstrap?.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alertElement).close();
            }
        }, 5000);
    });
});
