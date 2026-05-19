(function () {
    const root = document.documentElement;
    const storedDarkMode = localStorage.getItem('darkMode') === 'true';
    const theme = root.dataset.serverTheme || root.getAttribute('data-theme') || localStorage.getItem('theme') || window.__currentTheme || 'ea';

    root.classList.toggle('dark', storedDarkMode);
    root.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    window.__currentTheme = theme;
})();

import './bootstrap';
import Alpine from 'alpinejs';
import AlpineToastr from 'alpine-toastr';
import autoAnimate from '@formkit/auto-animate';

console.log("[boot] EAJ APP BOOT START");

// Register Alpine Toastr Plugin BEFORE Alpine starts
Alpine.plugin(AlpineToastr);
window.AlpineToastr = AlpineToastr;

const animatedElements = new WeakSet();

function initAutoAnimate(root = document) {
    root.querySelectorAll('[data-auto-animate]').forEach((el) => {
        if (animatedElements.has(el)) return;
        autoAnimate(el, {
            duration: 220,
            easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
        });
        animatedElements.add(el);
    });
}

function refreshUi(root = document) {
    initAutoAnimate(root);
    window.refreshIcons?.();
}

function applyHrmsTheme(theme) {
    const selectedTheme = theme || document.documentElement.dataset.serverTheme || 'ea';
    document.documentElement.setAttribute('data-theme', selectedTheme);
    document.documentElement.dataset.serverTheme = selectedTheme;
    window.__currentTheme = selectedTheme;
    localStorage.setItem('theme', selectedTheme);
    window.dispatchEvent(new CustomEvent('hrms:theme-changed', { detail: { theme: selectedTheme } }));
    refreshUi();
}

window.applyHrmsTheme = applyHrmsTheme;
window.refreshUi = refreshUi;

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// DOM READY
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
document.addEventListener('DOMContentLoaded', () => {
    refreshUi();
    initializeApp();
});

// ============================================
// Tailwind CDN toast system
// ============================================

class ToastManager {
    constructor() {
        this.containers = {};
        this.toasts = [];
        this.positions = ['top-right', 'top-left', 'top-center', 'bottom-right', 'bottom-left', 'bottom-center'];
        this.init();
    }

    init() {
        this.positions.forEach((position) => {
            let container = document.querySelector(`[data-toast-viewport="${position}"]`);
            if (!container) {
                container = document.createElement('div');
                container.dataset.toastViewport = position;
                container.className = this.getContainerClasses(position);
                document.body.appendChild(container);
            }
            this.containers[position] = container;
        });
    }

    getContainerClasses(position) {
        const base = 'pointer-events-none fixed z-[9999] flex w-full max-w-sm flex-col gap-3 p-4 sm:p-5';
        const positions = {
            'top-right': 'right-0 top-0 items-end',
            'top-left': 'left-0 top-0 items-start',
            'top-center': 'left-1/2 top-0 -translate-x-1/2 items-center',
            'bottom-right': 'bottom-0 right-0 items-end',
            'bottom-left': 'bottom-0 left-0 items-start',
            'bottom-center': 'bottom-0 left-1/2 -translate-x-1/2 items-center',
        };
        return `${base} ${positions[position] || positions['top-right']}`;
    }

    getVariant(variant) {
        const variants = {
            success: { icon: 'check-circle-2', iconClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300', borderClass: 'border-emerald-500/25', progressClass: 'bg-emerald-500' },
            error: { icon: 'circle-alert', iconClass: 'bg-red-500/10 text-red-600 dark:text-red-300', borderClass: 'border-red-500/25', progressClass: 'bg-red-500' },
            warning: { icon: 'triangle-alert', iconClass: 'bg-amber-500/10 text-amber-600 dark:text-amber-300', borderClass: 'border-amber-500/25', progressClass: 'bg-amber-500' },
            info: { icon: 'info', iconClass: 'bg-sky-500/10 text-sky-600 dark:text-sky-300', borderClass: 'border-sky-500/25', progressClass: 'bg-sky-500' },
            default: { icon: 'bell', iconClass: 'bg-primary/10 text-primary', borderClass: 'border-border', progressClass: 'bg-primary' },
        };
        return variants[variant] || variants.default;
    }

    getContainer(position) {
        return this.containers[position] || this.containers['top-right'];
    }

    show(options = {}) {
        const { title = 'Notification', description = '', variant = 'default', duration = 5000, position = 'top-right', action = null } = options;
        const toast = this.createToastElement(title, description, variant, duration, action);
        this.getContainer(position).appendChild(toast);
        this.toasts.push(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'scale-95', 'opacity-0');
            toast.classList.add('translate-y-0', 'scale-100', 'opacity-100');
        });

        if (duration !== Infinity) setTimeout(() => this.remove(toast), duration);
        refreshUi(toast);
        return toast;
    }

    createToastElement(title, description, variant, duration, action) {
        const meta = this.getVariant(variant);
        const toast = document.createElement('div');
        toast.className = `pointer-events-auto w-full overflow-hidden rounded-xl border ${meta.borderClass} bg-card/95 text-card-foreground shadow-2xl shadow-black/10 backdrop-blur transition duration-200 ease-out translate-y-2 scale-95 opacity-0`;
        toast.innerHTML = `
            <div class="flex items-start gap-3 p-4">
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${meta.iconClass}"><i data-lucide="${meta.icon}" class="h-4 w-4"></i></div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold leading-5 text-foreground">${this.escapeHtml(title)}</p>
                    ${description ? `<p class="mt-1 text-sm leading-5 text-muted-foreground">${this.escapeHtml(description)}</p>` : ''}
                    ${action ? `<button type="button" data-toast-action class="mt-3 inline-flex h-8 items-center rounded-lg border border-border bg-background px-3 text-xs font-semibold text-foreground transition hover:bg-accent hover:text-accent-foreground">${this.escapeHtml(action.label)}</button>` : ''}
                </div>
                <button type="button" data-toast-close class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-accent hover:text-accent-foreground" aria-label="Close"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
            ${duration !== Infinity ? `<div class="h-1 bg-muted"><div data-toast-progress class="h-full ${meta.progressClass} transition-[width] ease-linear" style="width:100%; transition-duration:${duration}ms"></div></div>` : ''}
        `;

        toast.querySelector('[data-toast-close]')?.addEventListener('click', () => this.remove(toast));
        toast.querySelector('[data-toast-action]')?.addEventListener('click', () => {
            action?.onClick?.();
            this.remove(toast);
        });

        const progress = toast.querySelector('[data-toast-progress]');
        if (progress && duration !== Infinity) requestAnimationFrame(() => { progress.style.width = '0%'; });
        return toast;
    }

    remove(toast) {
        if (!toast?.parentNode) return;
        toast.classList.add('translate-y-2', 'scale-95', 'opacity-0');
        setTimeout(() => {
            toast.remove();
            this.toasts = this.toasts.filter((item) => item !== toast);
        }, 180);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    success(title, description, duration = 5000, position = 'top-right') { return this.show({ title, description, variant: 'success', duration, position }); }
    error(title, description, duration = 5000, position = 'top-right') { return this.show({ title, description, variant: 'error', duration, position }); }
    warning(title, description, duration = 5000, position = 'top-right') { return this.show({ title, description, variant: 'warning', duration, position }); }
    info(title, description, duration = 5000, position = 'top-right') { return this.show({ title, description, variant: 'info', duration, position }); }
    default(title, description, duration = 5000, position = 'top-right') { return this.show({ title, description, variant: 'default', duration, position }); }
}

window.Toast = new ToastManager();

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// MAIN INIT
// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function initializeApp() {

    console.log("ðŸ“¦ Initializing system...");

    try {

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // jQuery
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const $module = await import('jquery');
        const $ = $module.default;

        window.$ = $;
        window.jQuery = $;

        console.log("[ok] jQuery loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Alpine Collapse Plugin
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const collapse = (await import('@alpinejs/collapse')).default;

        Alpine.plugin(collapse);
        window.Alpine = Alpine;
        
        // Start Alpine if not already started
        if (!Alpine.started) {
            Alpine.start();
        }

        console.log("[ok] Alpine + Toastr loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // ðŸ”¥ LUCIDE (FIXED + CLEAN)
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const { createIcons, icons } = await import('lucide');

        function refreshIcons() {
            console.log("ðŸ” Rendering Lucide icons...");
            createIcons({ icons });
        }

        window.refreshIcons = refreshIcons;

        // initial run
        refreshIcons();

        console.log("[ok] Lucide loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Moment
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const moment = (await import('moment')).default;
        window.moment = moment;

        console.log("[ok] Moment loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // SweetAlert2
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const Swal = (await import('sweetalert2')).default;
        window.Swal = Swal;

        console.log("[ok] SweetAlert2 loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Chart.js
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const chartJS = await import('chart.js');
        chartJS.Chart.register(...chartJS.registerables);
        window.Chart = chartJS.Chart;

        console.log("[ok] Chart.js loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // ApexCharts
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const ApexCharts = (await import('apexcharts')).default;
        window.ApexCharts = ApexCharts;

        console.log("[ok] ApexCharts loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Select2
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        await import('select2/dist/js/select2.full.min.js');

        console.log("[ok] Select2 loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // DataTables
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const DataTable = (await import('datatables.net')).default;
        await import('datatables.net-responsive');
        await import('datatables.net-buttons');

        console.log("[ok] DataTables loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // JSZip
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const JSZip = (await import('jszip')).default;
        window.JSZip = JSZip;

        console.log("[ok] JSZip loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // PDFMake
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const pdfMake = (await import('pdfmake/build/pdfmake')).default;
        const pdfFonts = (await import('pdfmake/build/vfs_fonts')).default;

        pdfMake.vfs = pdfFonts?.pdfMake?.vfs ?? pdfFonts;
        window.pdfMake = pdfMake;

        console.log("[ok] PDFMake loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Flatpickr
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const flatpickr = (await import('flatpickr')).default;
        window.flatpickr = flatpickr;

        console.log("[ok] Flatpickr loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // Daterangepicker
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        await import('daterangepicker');

        console.log("[ok] Daterangepicker loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // FullCalendar
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        const { Calendar } = await import('@fullcalendar/core');
        const { Draggable } = await import('@fullcalendar/interaction');

        const dayGridPlugin = (await import('@fullcalendar/daygrid')).default;
        const timeGridPlugin = (await import('@fullcalendar/timegrid')).default;
        const interactionPlugin = (await import('@fullcalendar/interaction')).default;

        class BundledCalendar extends Calendar {
            constructor(el, options = {}) {
                options.plugins = [
                    ...(options.plugins || []),
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin,
                ];
                super(el, options);
            }
        }

        window.FullCalendar = {
            Calendar: BundledCalendar,
            Draggable
        };

        console.log("[ok] FullCalendar loaded");

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // TOAST DEMO (Optional - remove in production)
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        setTimeout(() => {
            if (window.Toast) {
                console.log("[ok] Toast system ready");
            }
        }, 1000);

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // INIT PLUGINS
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        function initPlugins() {

            if ($ && $.fn) {

                if ($.fn.select2) {
                    $('.select2').select2();
                }

                if ($.fn.DataTable) {
                    $('.datatable').each(function () {
                        if (!$.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable({
                                responsive: true,
                                pageLength: 10
                            });
                        }
                    });
                }
            }

            if (window.flatpickr) {
                document.querySelectorAll('.datepicker').forEach(el => {
                    flatpickr(el);
                });
            }

            refreshUi();
        }

        initPlugins();

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // LIVEWIRE SUPPORT
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        document.addEventListener('livewire:navigated', () => {
            console.log("ðŸ” Livewire navigated");

            initPlugins();
            Alpine.initTree(document.body);
            refreshUi();
        });

        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        // DEBUG STATUS
        // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        console.log("ðŸŽ‰ SYSTEM READY");
        console.table({
            jQuery: !!window.$,
            Alpine: !!window.Alpine,
            AlpineToastr: !!window.AlpineToastr,
            Toast: !!window.Toast,
            Lucide: !!window.refreshIcons,
            moment: !!window.moment,
            Swal: !!window.Swal,
            Chart: !!window.Chart,
            ApexCharts: !!window.ApexCharts,
            flatpickr: !!window.flatpickr,
            JSZip: !!window.JSZip,
            pdfMake: !!window.pdfMake,
            FullCalendar: !!window.FullCalendar
        });

    } catch (err) {
        console.error("[error] APP FAILED:", err);
    }
}

// Make Alpine available globally
window.Alpine = Alpine;

