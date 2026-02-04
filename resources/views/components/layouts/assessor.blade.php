<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Penilai' }} - SPK Penilaian Guru AHP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-base-200">
    <div x-data="{ sidebarOpen: true, mobileSidebarOpen: false }" class="flex min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside
            :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen, 'lg:translate-x-0': true, 'lg:w-64': sidebarOpen, 'lg:w-20': !sidebarOpen}"
            class="fixed lg:relative z-30 flex flex-col w-64 min-h-screen bg-base-100 shadow-xl transition-all duration-300 transform -translate-x-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-base-200">
                <div class="flex items-center gap-3" x-show="sidebarOpen">
                    <div class="w-10 h-10 rounded-lg bg-secondary flex items-center justify-center">
                        <svg class="w-6 h-6 text-secondary-content" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg">Tim Penilai</span>
                </div>
                <button @click="mobileSidebarOpen = false" class="lg:hidden btn btn-ghost btn-sm btn-circle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-2">
                <ul class="menu menu-md gap-1 w-full">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('assessor.dashboard') }}"
                            class="{{ request()->routeIs('assessor.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarOpen">Dashboard</span>
                        </a>
                    </li>

                    <!-- Divider: Penilaian -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Penilaian KPI</span></li>
                    <li>
                        <a href="{{ route('assessor.assessments.index') }}"
                            class="{{ request()->routeIs('assessor.assessments.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Input Nilai</span>
                        </a>
                    </li>

                    <!-- Divider: Hasil -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Hasil</span></li>
                    <li>
                        <a href="{{ route('assessor.results.index') }}"
                            class="{{ request()->routeIs('assessor.results.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Lihat Hasil</span>
                        </a>
                    </li>

                    <!-- Divider: Akun -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Akun</span></li>
                    <li>
                        <a href="{{ route('assessor.profile.edit') }}"
                            class="{{ request()->routeIs('assessor.profile.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span x-show="sidebarOpen">Profil Saya</span>
                        </a>
                    </li>
                </ul>
            </nav>


        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar -->
            <header class="sticky top-0 z-10 bg-base-100 border-b border-base-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <!-- Left: Mobile Menu & Breadcrumb -->
                    <div class="flex items-center gap-4">
                        <button @click="mobileSidebarOpen = true" class="lg:hidden btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Breadcrumb -->
                        <div class="text-sm breadcrumbs hidden sm:block">
                            <ul>
                                <li><a href="{{ route('assessor.dashboard') }}">Penilai</a></li>
                                @if(isset($breadcrumbs))
                                {{ $breadcrumbs }}
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Right: Theme, User -->
                    <div class="flex items-center gap-2">
                        <!-- Theme Switcher -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </label>
                            <ul tabindex="0"
                                class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                <li><a onclick="document.documentElement.setAttribute('data-theme', 'light')">🌞
                                        Light</a></li>
                                <li><a onclick="document.documentElement.setAttribute('data-theme', 'dark')">🌙 Dark</a>
                                </li>
                                <li><a onclick="document.documentElement.setAttribute('data-theme', 'corporate')">🏢
                                        Corporate</a></li>
                                <li><a onclick="document.documentElement.setAttribute('data-theme', 'emerald')">💚
                                        Emerald</a></li>
                            </ul>
                        </div>

                        <!-- User Menu -->
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-secondary text-secondary-content rounded-full w-8">
                                        <span class="text-sm">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="hidden md:inline">{{ auth()->user()->name ?? 'Penilai' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </label>
                            <ul tabindex="0"
                                class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                <li class="menu-title">
                                    <span>{{ auth()->user()->email ?? 'assessor@example.com' }}</span>
                                </li>
                                <li><a href="{{ route('assessor.profile.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profil Saya
                                    </a></li>
                                <div class="divider my-1"></div>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
                @endif

                <!-- Page Header -->
                @if(isset($header))
                <div class="mb-6">
                    {{ $header }}
                </div>
                @endif

                <!-- Main Slot -->
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-base-200 bg-base-100 py-4 px-6">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm text-base-content/60">
                    <span>© {{ date('Y') }} SPK Penilaian Guru AHP. All rights reserved.</span>
                    <span>Panel Penilai v1.0.0</span>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>