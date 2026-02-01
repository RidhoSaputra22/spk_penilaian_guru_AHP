<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Panel Guru' }} - SPK Penilaian Guru AHP</title>
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
                    <div class="w-10 h-10 rounded-lg bg-accent flex items-center justify-center">
                        <svg class="w-6 h-6 text-accent-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg">Panel Guru</span>
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
                <ul class="menu menu-md gap-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('teacher.dashboard') }}"
                            class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarOpen">Dashboard</span>
                        </a>
                    </li>

                    <!-- Divider: Penilaian -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Penilaian</span></li>
                    <li>
                        <a href="{{ route('teacher.status.index') }}"
                            class="{{ request()->routeIs('teacher.status.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span x-show="sidebarOpen">Status Penilaian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.evidence.index') }}"
                            class="{{ request()->routeIs('teacher.evidence.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span x-show="sidebarOpen">Upload Bukti</span>
                        </a>
                    </li>

                    <!-- Divider: Hasil -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Hasil</span></li>
                    <li>
                        <a href="{{ route('teacher.results.index') }}"
                            class="{{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Hasil Penilaian</span>
                        </a>
                    </li>

                    <!-- Divider: Akun -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Akun</span></li>
                    <li>
                        <a href="{{ route('teacher.profile.edit') }}"
                            class="{{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span x-show="sidebarOpen">Profil Saya</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-base-200">
                <div class="flex items-center gap-3" x-show="sidebarOpen">
                    <div class="avatar placeholder">
                        <div class="w-10 rounded-full bg-accent text-accent-content">
                            <span>{{ substr(auth()->user()->name ?? 'G', 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'Guru' }}</p>
                        <p class="text-xs text-base-content/60 truncate">Guru</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm btn-circle" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
                <div x-show="!sidebarOpen" class="flex justify-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm btn-circle" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-screen">
            <!-- Top Navbar -->
            <header class="sticky top-0 z-10 bg-base-100 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4">
                    <div class="flex items-center gap-2">
                        <!-- Mobile Menu Toggle -->
                        <button @click="mobileSidebarOpen = true" class="btn btn-ghost btn-sm btn-circle lg:hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <!-- Desktop Sidebar Toggle -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden lg:flex btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <!-- Breadcrumbs -->
                        <div class="text-sm breadcrumbs hidden sm:block">
                            <ul>
                                <li><a href="{{ route('teacher.dashboard') }}">Guru</a></li>
                                {{ $breadcrumbs ?? '' }}
                            </ul>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Theme Toggle -->
                        <label class="swap swap-rotate btn btn-ghost btn-sm btn-circle">
                            <input type="checkbox" class="theme-controller" value="dark" />
                            <svg class="swap-on w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24">
                                <path
                                    d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                            </svg>
                            <svg class="swap-off w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24">
                                <path
                                    d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                            </svg>
                        </label>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <x-ui.alert type="success" class="mb-4" dismissible>
                    {{ session('success') }}
                </x-ui.alert>
                @endif

                @if(session('error'))
                <x-ui.alert type="error" class="mb-4" dismissible>
                    {{ session('error') }}
                </x-ui.alert>
                @endif

                @if(session('warning'))
                <x-ui.alert type="warning" class="mb-4" dismissible>
                    {{ session('warning') }}
                </x-ui.alert>
                @endif

                <!-- Header Section -->
                @if(isset($header))
                <div class="mb-6">
                    {{ $header }}
                </div>
                @endif

                <!-- Main Content -->
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 text-center text-sm text-base-content/60 border-t border-base-200">
                &copy; {{ date('Y') }} SPK Penilaian Guru AHP. All rights reserved.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
