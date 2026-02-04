<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Pengaturan</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold">Pengaturan</h1>
            <p class="text-base-content/60">Konfigurasi sistem penilaian</p>
        </div>
    </x-slot:header>



    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Menu -->
        <div class="lg:col-span-1">
            <ul class="menu bg-base-100 rounded-box shadow-sm w-full">
                <li class="menu-title">Umum</li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'institution']) }}"
                        class="{{ $tab === 'institution' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Institusi
                    </a></li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'scoring']) }}"
                        class="{{ $tab === 'scoring' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Skala Penilaian
                    </a></li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'groups']) }}"
                        class="{{ $tab === 'groups' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Kelompok Guru
                    </a></li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'ahp']) }}"
                        class="{{ $tab === 'ahp' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        AHP
                    </a></li>
                <!-- <li class="menu-title">Sistem</li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'email']) }}"
                        class="{{ $tab === 'email' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Email
                    </a></li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'notifications']) }}"
                        class="{{ $tab === 'notifications' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notifikasi
                    </a></li>
                <li><a href="{{ route('admin.settings.index', ['tab' => 'backup']) }}"
                        class="{{ $tab === 'backup' ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        Backup
                    </a></li> -->
            </ul>
        </div>

        <!-- Content -->
        <div class="lg:col-span-3 space-y-6">
            @if($tab === 'institution')
            @include('admin.settings.partials.institution')
            @elseif($tab === 'scoring')
            @include('admin.settings.partials.scoring')
            @elseif($tab === 'groups')
            @include('admin.settings.partials.groups')
            @elseif($tab === 'ahp')
            @include('admin.settings.partials.ahp')
            @elseif($tab === 'email')
            @include('admin.settings.partials.email')
            @elseif($tab === 'notifications')
            @include('admin.settings.partials.notifications')
            @elseif($tab === 'backup')
            @include('admin.settings.partials.backup')
            @endif
        </div>
    </div>
</x-layouts.admin>