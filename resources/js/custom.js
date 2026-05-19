(function () {
    const queue = [];

    function normalizeType(type) {
        return ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
    }

    function showToast(type, title, message, options = {}) {
        const variant = normalizeType(type);
        if (!window.Toast) return false;

        if (typeof window.Toast[variant] === 'function') {
            window.Toast[variant](title, message, options.duration ?? 5000, options.position ?? 'top-right');
            return true;
        }

        if (typeof window.Toast.show === 'function') {
            window.Toast.show({
                title,
                description: message,
                variant,
                duration: options.duration ?? 5000,
                position: options.position ?? 'top-right',
            });
            return true;
        }

        return false;
    }

    function flushQueue() {
        while (queue.length && window.Toast) {
            const item = queue.shift();
            showToast(item.type, item.title, item.message, item.options);
        }
    }

    function enqueue(type, title, message, options = {}) {
        if (!showToast(type, title, message, options)) {
            queue.push({ type, title, message, options });
            setTimeout(flushQueue, 150);
        }
    }

    window.safeToast = {
        success: (title, message, options = {}) => enqueue('success', title, message, options),
        error: (title, message, options = {}) => enqueue('error', title, message, options),
        warning: (title, message, options = {}) => enqueue('warning', title, message, options),
        info: (title, message, options = {}) => enqueue('info', title, message, options),
    };

    window.addEventListener('hrms:theme-changed', () => window.refreshUi?.());

    function readFlashMessages() {
        const flashContainer = document.getElementById('flash-messages');
        const messagesAttr = flashContainer?.getAttribute('data-flash-messages');
        if (!messagesAttr) return [];

        try {
            return JSON.parse(messagesAttr);
        } catch (error) {
            console.error('Failed to parse flash messages:', error);
            return [];
        }
    }

    function showFlashMessages(delay = 300) {
        readFlashMessages().forEach((message) => {
            setTimeout(() => {
                window.safeToast?.[normalizeType(message.type)]?.(message.title, message.message);
            }, delay);
        });
    }

    function showUrlMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const errorMessage = urlParams.get('error_message');
        const success = urlParams.get('success');
        const successMessage = urlParams.get('success_message');

        if (error) {
            setTimeout(() => window.safeToast.error('Authentication Error', errorMessage || error), 300);
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        if (success) {
            setTimeout(() => window.safeToast.success('Success', successMessage || success), 300);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        showFlashMessages(400);
        showUrlMessages();
    });

    document.addEventListener('livewire:navigated', () => showFlashMessages(250));
})();
