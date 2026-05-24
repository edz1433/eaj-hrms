@php
    $resolvedTheme = $sysTheme ?? 'ea';
    $appTitle = $sysName ?? 'EAJ HRMS';
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $resolvedTheme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appTitle }} - @yield('pageTitle', 'Time Entry')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function () {
            var savedTheme = '{{ $resolvedTheme }}';
            localStorage.setItem('theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
            if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css'])
    <script defer src="https://unpkg.com/lucide@latest"></script>
    <link rel="shortcut icon" href="{{ asset('Uploads/ease-icon.png') }}">
    @stack('styles')
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    <header class="sticky top-0 z-20 border-b border-border bg-background/95 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                    <img src="{{ asset('Uploads/ease-icon.png') }}" alt="EAJ" class="h-5 w-5 object-contain">
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">{{ $appTitle }}</p>
                    <p class="truncate text-xs text-muted-foreground">Time Entry</p>
                </div>
            </div>
            <button type="button" onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('darkMode', document.documentElement.classList.contains('dark') ? 'true' : 'false')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-foreground transition hover:bg-accent" aria-label="Toggle theme">
                <i data-lucide="sun-moon" class="h-4 w-4"></i>
            </button>
        </div>
    </header>
    <main class="min-h-[calc(100svh-7rem)] p-3 pb-24 sm:p-6">
        @yield('body')
    </main>
    <footer class="fixed bottom-0 left-0 right-0 z-20 border-t border-border bg-background/95 backdrop-blur sm:static">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <div class="flex min-w-0 items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i data-lucide="shield-check" class="h-4 w-4"></i>
                </span>
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-foreground">Secure Time Entry</p>
                    <p class="truncate text-[11px] text-muted-foreground">QR + live face + geo verification</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 rounded-full border border-border bg-card px-3 py-1.5 text-[11px] font-medium text-muted-foreground">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Ready
            </div>
        </div>
    </footer>
    <script>
        window.Toast = window.Toast || {
            show(kind, title, message) {
                const toast = document.createElement('div');
                toast.className = 'fixed left-4 right-4 top-16 z-[9999] rounded-lg border border-border bg-card p-3 text-sm text-card-foreground shadow-xl sm:left-auto sm:w-96';
                toast.innerHTML = `<p class="font-semibold text-foreground">${title}</p>${message ? `<p class="mt-1 text-muted-foreground">${message}</p>` : ''}`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), kind === 'error' ? 5200 : 3200);
            },
            success(title, message) { this.show('success', title, message); },
            error(title, message) { this.show('error', title, message); },
            warning(title, message) { this.show('warning', title, message); },
            info(title, message) { this.show('info', title, message); },
        };
        document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons?.());
    </script>
    @stack('scripts')
</body>
</html>
