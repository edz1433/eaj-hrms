import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/custom.js',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'resources/css/**/*.css',
                'resources/js/**/*.js',
                'app/Http/**/*.php',
                'routes/**/*.php',
            ],
        }),
    ],

    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: false,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            usePolling: false,
            interval: 100,
        },
    },

    optimizeDeps: {
        include: [
            'jquery',
            'alpinejs',
            '@alpinejs/collapse',
            '@formkit/auto-animate',
            'alpine-toastr',
            'moment',
            'sweetalert2',
            'chart.js',
            'apexcharts',
            'select2',
            'datatables.net',
            'datatables.net-responsive',
            'datatables.net-buttons',
            'jszip',
            'pdfmake',
            'flatpickr',
            'daterangepicker',
            '@fullcalendar/core',
            '@fullcalendar/interaction',
            '@fullcalendar/daygrid',
            '@fullcalendar/timegrid',
        ],
    },

    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['jquery', 'alpinejs', '@alpinejs/collapse', '@formkit/auto-animate', 'alpine-toastr'],
                    charts: ['chart.js', 'apexcharts'],
                    tables: ['datatables.net', 'datatables.net-responsive', 'datatables.net-buttons'],
                    calendar: ['@fullcalendar/core', '@fullcalendar/interaction', '@fullcalendar/daygrid', '@fullcalendar/timegrid'],
                    ui: ['select2', 'flatpickr', 'daterangepicker'],
                    pdf: ['pdfmake/build/pdfmake'],
                    utils: ['moment', 'sweetalert2', 'jszip'],
                },
            },
        },
        chunkSizeWarningLimit: 1200,
    },
});
