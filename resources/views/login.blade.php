@php
    $resolvedTheme = $sysTheme ?? 'ea';
    $hexToHsl = function (?string $hex): ?string {
        if (!$hex || !preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $m)) {
            return null;
        }

        $hex = $m[1];
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            $h = match ($max) {
                $r => (($g - $b) / $d + ($g < $b ? 6 : 0)),
                $g => (($b - $r) / $d + 2),
                default => (($r - $g) / $d + 4),
            } / 6;
        }

        return round($h * 360) . ' ' . round($s * 100) . '% ' . round($l * 100) . '%';
    };
    $primaryHsl = $hexToHsl($sysPrimaryColor ?? null);
    $accentHsl = $hexToHsl($sysAccentColor ?? null);
    $appTitle = $sysName ?? 'EAJ HRMS';
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $resolvedTheme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appTitle }} - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                        colors: {
                            background: 'hsl(var(--background) / <alpha-value>)',
                            foreground: 'hsl(var(--foreground) / <alpha-value>)',
                            card: 'hsl(var(--card) / <alpha-value>)',
                            'card-foreground': 'hsl(var(--card-foreground) / <alpha-value>)',
                            popover: 'hsl(var(--popover) / <alpha-value>)',
                            'popover-foreground': 'hsl(var(--popover-foreground) / <alpha-value>)',
                            primary: 'hsl(var(--primary) / <alpha-value>)',
                            'primary-foreground': 'hsl(var(--primary-foreground) / <alpha-value>)',
                            secondary: 'hsl(var(--secondary) / <alpha-value>)',
                            'secondary-foreground': 'hsl(var(--secondary-foreground) / <alpha-value>)',
                            muted: 'hsl(var(--muted) / <alpha-value>)',
                            'muted-foreground': 'hsl(var(--muted-foreground) / <alpha-value>)',
                            accent: 'hsl(var(--accent) / <alpha-value>)',
                            'accent-foreground': 'hsl(var(--accent-foreground) / <alpha-value>)',
                            destructive: 'hsl(var(--destructive) / <alpha-value>)',
                            'destructive-foreground': 'hsl(var(--destructive-foreground) / <alpha-value>)',
                            border: 'hsl(var(--border) / <alpha-value>)',
                            input: 'hsl(var(--input) / <alpha-value>)',
                            ring: 'hsl(var(--ring) / <alpha-value>)',
                            sidebar: 'hsl(var(--sidebar) / <alpha-value>)',
                            'sidebar-foreground': 'hsl(var(--sidebar-foreground) / <alpha-value>)',
                            'sidebar-primary': 'hsl(var(--sidebar-primary) / <alpha-value>)',
                            'sidebar-primary-foreground': 'hsl(var(--sidebar-primary-foreground) / <alpha-value>)',
                            'sidebar-accent': 'hsl(var(--sidebar-accent) / <alpha-value>)',
                            'sidebar-accent-foreground': 'hsl(var(--sidebar-accent-foreground) / <alpha-value>)',
                            'sidebar-border': 'hsl(var(--sidebar-border) / <alpha-value>)',
                        },
                    },
                },
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        (function () {
            var savedTheme = '{{ $resolvedTheme }}';
            var uiVersion = 'tailwind-cdn-theme-v3';
            if (localStorage.getItem('hrmsUiVersion') !== uiVersion) {
                localStorage.setItem('darkMode', 'false');
                localStorage.setItem('hrmsUiVersion', uiVersion);
            }
            localStorage.setItem('theme', savedTheme);
            document.documentElement.setAttribute('data-theme', savedTheme);
            var darkMode = localStorage.getItem('darkMode');
            if (darkMode === 'true') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
            window.__currentTheme = savedTheme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/custom.js'])
    <style id="theme-token-safety">
        html:not(.dark) {
            --background: 266 100% 98%;
            --foreground: 263 42% 15%;
            --card: 0 0% 100%;
            --card-foreground: 263 42% 15%;
            --border: 268 45% 88%;
            --input: 268 45% 88%;
            --muted: 268 100% 96%;
            --muted-foreground: 263 18% 44%;
            --sidebar: var(--background);
            --sidebar-foreground: var(--foreground);
            --sidebar-border: var(--border);
            --sidebar-accent: var(--accent);
            --sidebar-accent-foreground: var(--accent-foreground);
        }
        body, .bg-background { background-color: hsl(var(--background)) !important; color: hsl(var(--foreground)) !important; }
        .bg-card { background-color: hsl(var(--card)) !important; }
        .text-foreground { color: hsl(var(--foreground)) !important; }
        .text-card-foreground { color: hsl(var(--card-foreground)) !important; }
        .text-muted-foreground { color: hsl(var(--muted-foreground)) !important; }
        .bg-primary { background-color: hsl(var(--primary)) !important; }
        .text-primary { color: hsl(var(--primary)) !important; }
        .text-primary-foreground { color: hsl(var(--primary-foreground)) !important; }
        .border-border { border-color: hsl(var(--border)) !important; }
    </style>
    @if($primaryHsl || $accentHsl)
        <style>
            html {
                @if($primaryHsl)
                    --primary: {{ $primaryHsl }} !important;
                    --ring: {{ $primaryHsl }} !important;
                @endif
                @if($accentHsl)
                    --accent: {{ $accentHsl }} !important;
                @endif
            }
        </style>
    @endif
    <link rel="shortcut icon" href="{{ asset('Uploads/ease-icon.png') }}">
</head>

<body class="relative min-h-screen overflow-x-hidden bg-background font-sans text-foreground antialiased">

    {{-- Flash Messages Data - passed to toast component --}}
    @php
        $flashMessages = [];
        $messageTypes = ['error' => 'Login Failed', 'success' => 'Success', 'warning' => 'Notice', 'info' => 'Information'];
        foreach($messageTypes as $type => $title) {
            if($message = session($type)) {
                $flashMessages[] = ['type' => $type, 'title' => $title, 'message' => $message];
            }
        }
    @endphp

    {{-- Animated Background Blobs (shadcn style) --}}
    <div class="pointer-events-none absolute -top-40 left-1/4 h-[32rem] w-[32rem] rounded-full bg-primary/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-40 -right-32 h-[28rem] w-[28rem] rounded-full bg-accent/60 blur-3xl"></div>
    <div class="pointer-events-none absolute left-[-10rem] top-1/3 h-[24rem] w-[24rem] rounded-full bg-muted blur-3xl"></div>

    {{-- Login Card - fully shadcn/ui styled --}}
    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative z-10">
        <div class="w-full max-w-md">
            
            {{-- Secure badge (glassmorphism) --}}
            <div class="flex justify-center mb-5">
                <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background/70 backdrop-blur-sm px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    Secure Access
                </div>
            </div>

            {{-- Main Card - shadcn card component --}}
            <div class="overflow-hidden rounded-3xl border border-border/70 bg-card/95 text-card-foreground shadow-2xl backdrop-blur">
                {{-- Card Header with Logo --}}
                <div class="border-b border-border/70 px-8 pb-5 pt-8 text-center">
                    <div class="flex justify-center mb-3">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 transition-transform hover:scale-105">
                            <img src="{{ asset('Uploads/ease-icon.png') }}" alt="EAJ Logo" class="h-10 w-10 object-contain">
                        </div>
                    </div>
                    <div>
                        <h1 class="bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-2xl font-bold tracking-tight text-transparent">
                            {{ $appTitle }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">Sign in to access your account</p>
                    </div>
                </div>

                {{-- Card Content - login action --}}
                <div class="space-y-5 p-8">
                    
                    {{-- Google Login Button (shadcn primary button) --}}
                    <a href="{{ route('google.login') }}" 
                       class="inline-flex h-11 w-full items-center justify-center gap-3 rounded-2xl bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-xl active:translate-y-0">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#FFFFFF"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#FFFFFF"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FFFFFF"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#FFFFFF"/>
                        </svg>
                        Sign in with Google
                    </a>

                    {{-- Divider (shadcn style) --}}
                    <div class="relative my-2">
                        <div class="absolute inset-0 flex items-center">
                            <span class="w-full border-t border-border"></span>
                        </div>
                        <div class="relative flex justify-center text-xs uppercase">
                            <span class="bg-card px-2 text-muted-foreground">Protected Login</span>
                        </div>
                    </div>

                    {{-- Security Notice (shadcn alert style) --}}
                    <div class="flex gap-3 rounded-xl border border-border/60 bg-muted/30 p-4 transition-all hover:border-primary/20">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <i data-lucide="shield-check" class="h-4 w-4"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-medium text-foreground">Security Notice</p>
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Authorized access only. Login activity is monitored for security and audit purposes.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="mt-2 flex justify-center border-t border-border/70 px-8 py-5">
                    <p class="text-center text-xs text-muted-foreground">
                        &copy; {{ date('Y') }} {{ $appTitle }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash Messages converted to Toast (using your existing Toast system) --}}
    @if(!empty($flashMessages))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const messages = @json($flashMessages);
            const showToasts = () => {
                if (window.Toast) {
                    messages.forEach((msg) => {
                        const variant = msg.type;
                        window.Toast.show({
                            title: msg.title,
                            description: msg.message,
                            variant: variant,
                            duration: 6000,
                            position: 'bottom-center'
                        });
                    });
                } else {
                    setTimeout(showToasts, 200);
                }
            };
            showToasts();
        });
    </script>
    @endif

    {{-- Lucide Icons initialization (if needed) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
</body>
</html>







