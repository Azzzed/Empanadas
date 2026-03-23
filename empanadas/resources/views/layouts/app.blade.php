<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EmpanadasPOS') — @yield('subtitle', 'Sistema de Ventas')</title>

    {{-- Tailwind CSS CDN (reemplazar por vite en producción) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fff8f0',
                            100: '#feecd6',
                            200: '#fcd5a3',
                            300: '#f9b56a',
                            400: '#f7923a',
                            500: '#f47320', /* naranja principal */
                            600: '#e05a10',
                            700: '#b8440f',
                            800: '#923615',
                            900: '#782e16',
                            950: '#411408',
                        },
                        cream: {
                            50:  '#fdfaf5',
                            100: '#f9f1e4',
                            200: '#f2dfc0',
                            300: '#e8c98f',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Animaciones base --}}
    <style>
        [x-cloak] { display: none !important; }
        /* Transición suave global */
        *, *::before, *::after { transition-property: background-color, border-color, color, opacity, transform, box-shadow; transition-duration: 150ms; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }

        /* Card hover lift */
        .card-hover { @apply hover:shadow-lg hover:-translate-y-0.5 transform transition-all duration-200; }

        /* Fade-in para modales y alertas */
        .fade-in { animation: fadeIn 0.25s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* Slide-in carrito */
        .slide-in { animation: slideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes slideIn { from { opacity: 0; transform: scale(0.92) translateY(-8px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* Ripple al agregar producto */
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }
        .btn-ripple { position: relative; overflow: hidden; }
        .btn-ripple::after { content: ''; position: absolute; border-radius: 50%; background: rgba(255,255,255,0.35); width: 100px; height: 100px; margin: auto; top: -50px; left: -50px; pointer-events: none; transform: scale(0); }
        .btn-ripple:active::after { animation: ripple 0.4s ease-out; }

        /* Scrollbar thin */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f9f1e4; }
        ::-webkit-scrollbar-thumb { background: #f7923a; border-radius: 3px; }
    </style>

    @stack('styles')
</head>

<body class="h-full bg-cream-50 font-sans text-gray-800 antialiased">

{{-- ── Navbar ──────────────────────────────────────────────── --}}
<nav class="bg-brand-600 shadow-md sticky top-0 z-50">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-14">

            {{-- Logo --}}
            <a href="{{ route('pos.index') }}" class="flex items-center gap-2 text-white font-bold text-lg">
                <span class="text-2xl">🫓</span>
                <span class="hidden sm:block">EmpanadasPOS</span>
            </a>

            {{-- Navegación --}}
            <div class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('pos.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/20 hover:text-white
                          {{ request()->routeIs('pos.*') ? 'bg-white/25 text-white' : '' }}">
                    <span class="mr-1">🛒</span>
                    <span class="hidden sm:inline">POS</span>
                </a>
                <a href="{{ route('admin.products.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/20 hover:text-white
                          {{ request()->routeIs('admin.products.*') ? 'bg-white/25 text-white' : '' }}">
                    <span class="mr-1">📦</span>
                    <span class="hidden sm:inline">Productos</span>
                </a>
                <a href="{{ route('admin.customers.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/20 hover:text-white
                          {{ request()->routeIs('admin.customers.*') ? 'bg-white/25 text-white' : '' }}">
                    <span class="mr-1">👥</span>
                    <span class="hidden sm:inline">Clientes</span>
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/20 hover:text-white
                          {{ request()->routeIs('admin.reports.*') ? 'bg-white/25 text-white' : '' }}">
                    <span class="mr-1">📊</span>
                    <span class="hidden sm:inline">Informes</span>
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- ── Flash Messages ──────────────────────────────────────── --}}
@if(session('success') || session('error'))
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 pt-4" id="flash-container">
    @if(session('success'))
    <div class="fade-in flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm">
        <span class="text-xl">✅</span>
        <p class="text-sm font-medium">{{ session('success') }}</p>
        <button onclick="this.parentElement.remove()" class="ml-auto text-green-500 hover:text-green-700">✕</button>
    </div>
    @endif
    @if(session('error'))
    <div class="fade-in flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm">
        <span class="text-xl">⚠️</span>
        <p class="text-sm font-medium">{{ session('error') }}</p>
        <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">✕</button>
    </div>
    @endif
</div>
@endif

{{-- ── Contenido principal ─────────────────────────────────── --}}
<main class="@yield('main-class', 'max-w-screen-xl mx-auto px-4 sm:px-6 py-6')">
    @yield('content')
</main>

{{-- Auto-cerrar flash después de 4 s --}}
<script>
    setTimeout(() => {
        const fc = document.getElementById('flash-container');
        if (fc) { fc.style.opacity = '0'; fc.style.transition = 'opacity 0.5s'; setTimeout(() => fc.remove(), 500); }
    }, 4000);
</script>

@stack('scripts')
</body>
</html>
