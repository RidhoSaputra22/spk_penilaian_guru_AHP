<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} - SPK Penilaian Guru AHP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-base-200">
    <div x-data="{ sidebarOpen: true, mobileSidebarOpen: false }" class="flex min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

        <!-- Sidebar -->
        <aside
            :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen, 'lg:translate-x-0': true, 'lg:w-64': sidebarOpen, 'lg:w-20': !sidebarOpen}"
            class="fixed lg:relative z-30 flex flex-col w-64 min-h-screen bg-base-100 shadow-xl transition-all duration-300 transform -translate-x-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-base-200">
                <div class="flex items-center gap-3" x-show="sidebarOpen">
                    <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="font-bold text-lg">SPK Guru</span>
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
                        <a href="{{ route('admin.dashboard') }}"
                            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span x-show="sidebarOpen">Dashboard</span>
                        </a>
                    </li>

                    <!-- Divider: User & Access -->
                    <li class="menu-title" x-show="sidebarOpen"><span>User & Access</span></li>
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                            class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span x-show="sidebarOpen">Manajemen User</span>
                        </a>
                    </li>

                    <!-- Divider: Master Data -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Master Data</span></li>
                    <li>
                        <a href="{{ route('admin.teachers.index') }}"
                            class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <span x-show="sidebarOpen">Data Guru</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.assessors.index') }}"
                            class="{{ request()->routeIs('admin.assessors.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span x-show="sidebarOpen">Tim Penilai</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.periods.index') }}"
                            class="{{ request()->routeIs('admin.periods.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Periode Penilaian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.criteria.index') }}"
                            class="{{ request()->routeIs('admin.criteria.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span x-show="sidebarOpen">Kriteria & Indikator</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.scoring-scales.index') }}"
                            class="{{ request()->routeIs('admin.scoring-scales.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Skala Penilaian</span>
                        </a>
                    </li>

                    <!-- Divider: KPI Form -->
                    <li class="menu-title" x-show="sidebarOpen"><span>KPI Form Builder</span></li>
                    <li>
                        <a href="{{ route('admin.kpi-forms.index') }}"
                            class="{{ request()->routeIs('admin.kpi-forms.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Template Form KPI</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.kpi-assignments.index') }}"
                            class="{{ request()->routeIs('admin.kpi-assignments.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span x-show="sidebarOpen">Penugasan Form</span>
                        </a>
                    </li>

                    <!-- Divider: AHP -->
                    <li class="menu-title" x-show="sidebarOpen"><span>AHP Weighting</span></li>
                    <li>
                        <a href="{{ route('admin.ahp.index') }}"
                            class="{{ request()->routeIs('admin.ahp.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span x-show="sidebarOpen">Pembobotan AHP</span>
                        </a>
                    </li>

                    <!-- Divider: Results -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Hasil & Laporan</span></li>
                    <li>
                        <a href="{{ route('admin.assessments.index') }}"
                            class="{{ request()->routeIs('admin.assessments.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span x-show="sidebarOpen">Monitoring Penilaian</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.results.index') }}"
                            class="{{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Hasil & Ranking</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.index') }}"
                            class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span x-show="sidebarOpen">Export Laporan</span>
                        </a>
                    </li>

                    <!-- Divider: Settings -->
                    <li class="menu-title" x-show="sidebarOpen"><span>Pengaturan</span></li>
                    <li>
                        <a href="{{ route('admin.activity-logs.index') }}"
                            class="{{ request()->routeIs('admin.activity-logs.index') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-show="sidebarOpen">Activity Log</span>
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
                                <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                                @if(isset($breadcrumbs))
                                {{ $breadcrumbs }}
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Right: Theme, Notifications, User -->
                    <div class="flex items-center gap-2">


                        <!-- User Menu -->
                        <div class="dropdown dropdown-end ">
                            <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                                <div class="avatar placeholder">
                                    <div
                                        class="bg-primary text-primary-content rounded-full w-8 flex items-center justify-center">
                                        <span class="text-sm">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="hidden md:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </label>
                            <ul tabindex="0"
                                class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                <li class="menu-title"><span>{{ auth()->user()->email ?? 'admin@example.com' }}</span>
                                </li>
                                <li><a href="{{ route('admin.profile.edit') }}" class="flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profil Saya
                                    </a></li>
                                <li><a href="{{ route('admin.settings.index') }}" class="flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Pengaturan
                                    </a></li>
                                <div class="divider my-1"></div>
                                <li>
                                    <a href="">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="w-full text-left text-error flex gap-2 items-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </a>
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
                <x-ui.alert type="success" dismissible class="mb-4">
                    {{ session('success') }}
                </x-ui.alert>
                @endif

                @if(session('error'))
                <x-ui.alert type="error" dismissible class="mb-4">
                    {{ session('error') }}
                </x-ui.alert>
                @endif

                @if(session('warning'))
                <x-ui.alert type="warning" dismissible class="mb-4">
                    {{ session('warning') }}
                </x-ui.alert>
                @endif

                @if(session('info'))
                <x-ui.alert type="info" dismissible class="mb-4">
                    {{ session('info') }}
                </x-ui.alert>
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
                    <span>Version 1.0.0</span>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>
