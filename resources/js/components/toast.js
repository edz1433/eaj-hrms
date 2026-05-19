class ToastManager {
    constructor() {
        this.toasts = [];
        this.container = null;
        this.init();
    }

    init() {
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-3 max-w-sm';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(options) {
        const {
            title = '',
            description = '',
            variant = 'default',
            duration = 5000,
            action = null,
            onDismiss = null
        } = options;

        const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = this.getToastClasses(variant);
        
        toast.style.animation = 'slideInRight 0.3s ease-out';
        
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="toast-icon">
                    ${this.getIcon(variant)}
                </div>
                <div class="flex-1 min-w-0">
                    ${title ? `<h3 class="toast-title">${this.escapeHtml(title)}</h3>` : ''}
                    ${description ? `<p class="toast-description">${this.escapeHtml(description)}</p>` : ''}
                    ${action ? `
                        <button class="toast-action mt-3 inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                            ${this.escapeHtml(action.label)}
                        </button>
                    ` : ''}
                </div>
                <button class="toast-close ml-auto inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="toast-progress-bar">
                <div class="progress-bar-fill" style="animation: progressShrink ${duration}ms linear forwards"></div>
            </div>
        `;

        this.container.appendChild(toast);
        
        const toastObj = { id, element: toast, duration, onDismiss };
        this.toasts.push(toastObj);

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.dismiss(id));

        if (action) {
            const actionBtn = toast.querySelector('.toast-action');
            actionBtn.addEventListener('click', () => {
                action.onClick();
                this.dismiss(id);
            });
        }

        if (duration > 0) {
            setTimeout(() => this.dismiss(id), duration);
        }

        return id;
    }

    dismiss(id) {
        const toastIndex = this.toasts.findIndex(t => t.id === id);
        if (toastIndex === -1) return;

        const toast = this.toasts[toastIndex];
        toast.element.style.animation = 'slideOutRight 0.2s ease-in';
        
        setTimeout(() => {
            toast.element.remove();
            this.toasts.splice(toastIndex, 1);
            if (toast.onDismiss) toast.onDismiss();
        }, 200);
    }

    dismissAll() {
        this.toasts.forEach(toast => this.dismiss(toast.id));
    }

    getToastClasses(variant) {
        const baseClasses = 'relative w-full rounded-2xl border bg-white/95 px-4 py-4 shadow-xl ring-1 ring-slate-900/5 backdrop-blur-xl transition-all duration-200 dark:border-slate-800/80 dark:bg-slate-950/90 dark:ring-white/10';
        
        const variants = {
            default: 'border-slate-200/80 text-slate-950 dark:border-slate-800/80 dark:text-slate-50',
            destructive: 'border-rose-200/80 text-rose-900 dark:border-rose-800/80 dark:text-rose-100',
            success: 'border-emerald-200/80 text-emerald-900 dark:border-emerald-800/80 dark:text-emerald-100',
            warning: 'border-amber-200/80 text-amber-900 dark:border-amber-800/80 dark:text-amber-100'
        };

        return `${baseClasses} ${variants[variant] || variants.default}`;
    }

    getIcon(variant) {
        const icons = {
            default: `
                <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `,
            success: `
                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `,
            destructive: `
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            `,
            warning: `
                <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            `
        };

        return icons[variant] || icons.default;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Convenience methods
    success(title, description, options = {}) {
        return this.show({ title, description, variant: 'success', ...options });
    }

    error(title, description, options = {}) {
        return this.show({ title, description, variant: 'destructive', ...options });
    }

    warning(title, description, options = {}) {
        return this.show({ title, description, variant: 'warning', ...options });
    }

    info(title, description, options = {}) {
        return this.show({ title, description, variant: 'default', ...options });
    }
}

// Add CSS animations
if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes progressShrink {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }

        .toast-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .progress-bar-fill {
            height: 100%;
            background-color: currentColor;
            opacity: 0.3;
            animation-timing-function: linear;
        }
    `;
    document.head.appendChild(style);
}

// Create global instance
window.Toast = new ToastManager();

export default window.Toast;