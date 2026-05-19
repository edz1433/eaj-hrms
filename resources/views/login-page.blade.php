<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $sysName ?? 'EAJ HRMS' }} - Sign In</title>
    <link rel="shortcut icon" href="{{ asset('template/img/CPSU_L.png') }}">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#16a34a', dark: '#15803d', light: '#dcfce7' },
                        gold:  '#FFCB2C',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    keyframes: {
                        'fade-up': {
                            '0%':   { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: {
                        'fade-up': 'fade-up 0.4s ease both',
                    },
                }
            }
        }
    </script>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('template/plugins/fontawesome-free-v6/css/all.min.css') }}">

    <style>
        html, body { height: 100%; margin: 0; }
        .login-panel-bg {
            background-image: url('{{ asset('template/img/login-bg.jpg') }}');
            background-size: cover;
            background-position: center;
        }
        /* shadcn-style input ring */
        .sh-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #16a34a;
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-gray-50">

<div class="min-h-screen flex">

    {{-- ── LEFT PANEL ──────────────────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden">
        <div class="login-panel-bg absolute inset-0"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-green-900/80 to-green-700/60"></div>

        <div class="relative z-10 flex flex-col justify-between w-full p-14 text-white">
            {{-- Top brand --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('template/img/CPSU_L.png') }}" alt="CPSU" class="w-10 h-10 object-contain">
                <span class="text-lg font-semibold tracking-wide">{{ $sysName ?? 'EAJ HRMS' }}</span>
            </div>

            {{-- Center message --}}
            <div>
                <h1 class="text-4xl font-bold leading-tight">
                    Empowering People.<br>
                    <span class="text-yellow-300">Streamlining HR.</span>
                </h1>
                <p class="mt-4 text-green-100 text-base max-w-sm leading-relaxed">
                    Central Philippine State University's integrated Human Resource Information System &mdash;
                    from personnel records to performance management, all in one place.
                </p>

                {{-- Feature pills --}}
                <div class="mt-8 flex flex-wrap gap-3">
                    @foreach(['DTR & Attendance', 'Leave Management', 'PDS Records', 'SPMS / Performance', 'Payroll Link', 'Recruitment'] as $feat)
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20">
                        <i class="fas fa-check text-yellow-300 text-[10px]"></i> {{ $feat }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <p class="text-xs text-green-200/70">&copy; {{ date('Y') }} Central Philippine State University &mdash; All rights reserved.</p>
        </div>
    </div>

    {{-- ── RIGHT PANEL ─────────────────────────────────────────────────────── --}}
    <div class="w-full lg:w-[45%] flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-[400px] animate-fade-up">

            {{-- Mobile logo --}}
            <div class="flex flex-col items-center mb-8 lg:hidden">
                <img src="{{ asset('template/img/CPSU_L.png') }}" alt="CPSU" class="w-16 h-16 object-contain">
                <p class="mt-2 text-sm font-semibold text-gray-700">{{ $sysName ?? 'EAJ HRMS' }}</p>
            </div>

            {{-- Card --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_32px_rgba(0,0,0,0.08)] border border-gray-100 p-8">

                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Sign in</h2>
                    <p class="text-sm text-gray-500 mt-1">Enter your credentials to access your account.</p>
                </div>

                {{-- ── Alerts ── --}}
                @if(session('error'))
                <div class="mb-5 flex items-start gap-2.5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if(session('success'))
                <div class="mb-5 flex items-start gap-2.5 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                {{-- ── Form ── --}}
                <form action="{{ route('postLogin') }}" method="POST" id="loginForm" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-envelope text-sm"></i>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Enter your email address"
                                autocomplete="email"
                                autofocus
                                required
                                class="sh-input w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg transition-colors hover:border-gray-300 focus:bg-white"
                            >
                        </div>
                        @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                                class="sh-input w-full pl-10 pr-10 py-2.5 text-sm text-gray-900 bg-gray-50 border border-gray-200 rounded-lg transition-colors hover:border-gray-300 focus:bg-white"
                            >
                            <button
                                type="button"
                                id="togglePwd"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1"
                                aria-label="Toggle password visibility"
                            >
                                <i class="fas fa-eye text-sm" id="pwdIcon"></i>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        id="submitBtn"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
            </div>

            {{-- Role hint --}}
            <p class="mt-5 text-center text-xs text-gray-400">
                Administrators &amp; HR staff log in with their system username.<br>
                Employees use their assigned employee credentials.
            </p>
        </div>
    </div>

</div>

<script>
    // ── Password toggle ────────────────────────────────────────────────────────
    const togglePwd = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('password');
    const pwdIcon   = document.getElementById('pwdIcon');

    togglePwd.addEventListener('click', () => {
        const isText = pwdInput.type === 'text';
        pwdInput.type = isText ? 'password' : 'text';
        pwdIcon.classList.toggle('fa-eye', isText);
        pwdIcon.classList.toggle('fa-eye-slash', !isText);
    });

    // ── Loading state on submit ────────────────────────────────────────────────
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    loginForm.addEventListener('submit', function (e) {
        const username = document.getElementById('username').value.trim();
        const password = pwdInput.value.trim();

        if (!username || !password) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in&hellip;';
    });
</script>
</body>
</html>
