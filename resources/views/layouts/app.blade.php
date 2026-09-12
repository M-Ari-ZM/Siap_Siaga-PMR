<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#dc2626">
    <meta name="description" content="Sistem Pelaporan Tanggap Darurat Palang Merah Remaja (PMR) Sekolah">
    <title>@yield('title', 'Siap Siaga PMR')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet Map Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .leaflet-container {
            font-family: inherit;
            border-radius: 0.5rem;
            z-index: 10;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800 bg-white flex flex-col justify-between selection:bg-red-500 selection:text-white">

    <!-- Top Navbar -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <!-- Logo & Brand -->
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-base tracking-tight text-slate-900">SIAP SIAGA</span>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-red-600 text-white rounded">PMR</span>
                        </div>
                    </div>
                </a>

                <!-- Nav links / Auth Status -->
                <div class="flex items-center gap-2">
                    @auth
                        <div class="flex items-center gap-2">
                            <div class="text-right hidden sm:block">
                                <span class="text-xs font-bold text-slate-800 block">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] font-extrabold uppercase px-1.5 rounded bg-slate-100 text-slate-600 inline-block">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" title="Keluar" class="p-2 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-colors">
                                Daftar
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 pb-24 pt-4">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar -->
    @unless(request()->routeIs('login', 'register', 'student.emergency.create'))
    <div class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-slate-200">
        <div class="max-w-sm mx-auto px-4">
            <nav class="flex items-center justify-between py-2">

                @php
                    $userRole = Auth::check() ? Auth::user()->role : 'student';

                    if ($userRole === 'pmr') {
                        $homeRoute = route('pmr.dashboard');
                        $isHomeActive = request()->routeIs('pmr.*');
                        $activeColor = 'text-emerald-600';
                        $activeBg = 'bg-emerald-50';
                    } elseif ($userRole === 'admin') {
                        $homeRoute = route('admin.dashboard');
                        $isHomeActive = request()->routeIs('admin.*');
                        $activeColor = 'text-blue-600';
                        $activeBg = 'bg-blue-50';
                    } else {
                        $homeRoute = route('student.dashboard');
                        $isHomeActive = request()->routeIs('student.dashboard');
                        $activeColor = 'text-red-600';
                        $activeBg = 'bg-red-50';
                    }
                @endphp

                <!-- 1. Left Nav: Beranda -->
                <a href="{{ $homeRoute }}" class="flex flex-col items-center gap-0.5 py-1.5 px-5 rounded-lg transition-colors {{ $isHomeActive ? $activeColor . ' ' . $activeBg . ' font-bold' : 'text-slate-400 hover:text-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[11px]">Beranda</span>
                </a>

                <!-- 2. Center EMERGENCY Button -->
                <a href="{{ route('student.emergency.create') }}" class="flex flex-col items-center gap-0.5 px-4">
                    <div class="w-14 h-14 rounded-xl bg-red-600 hover:bg-red-700 flex flex-col items-center justify-center text-white transition-colors shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-black text-red-600 uppercase tracking-widest">SOS</span>
                </a>

                <!-- 3. Right Nav: Info -->
                <a href="{{ route('info') }}" class="flex flex-col items-center gap-0.5 py-1.5 px-5 rounded-lg transition-colors {{ request()->routeIs('info') ? 'text-slate-900 font-bold bg-slate-100' : 'text-slate-400 hover:text-slate-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[11px]">Info</span>
                </a>

            </nav>
        </div>
    </div>
    @endunless

    @stack('scripts')
</body>
</html>
