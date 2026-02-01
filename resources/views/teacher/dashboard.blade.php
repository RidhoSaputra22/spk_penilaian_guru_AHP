<x-layouts.teacher>
    <x-slot:title>Dashboard</x-slot:title>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $teacherProfile->subject ?? 'Guru' }} - Panel Penilaian Kinerja
                </p>
            </div>
        </div>
    </x-slot:header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Assessments -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['total_assessments'] }}</p>
                    <p class="text-sm text-base-content/60">Total Penilaian</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Pending -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
                    <p class="text-sm text-base-content/60">Sedang Berlangsung</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Completed -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-success">{{ $stats['completed'] }}</p>
                    <p class="text-sm text-base-content/60">Selesai</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Results Available -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-info">{{ $stats['results_available'] }}</p>
                    <p class="text-sm text-base-content/60">Hasil Tersedia</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Periods -->
        <x-ui.card>
            <h2 class="card-title mb-4">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Periode Aktif
            </h2>

            @if($activePeriods->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>Tidak ada periode penilaian aktif saat ini</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($activePeriods as $period)
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div>
                        <p class="font-medium">{{ $period->name }}</p>
                        <p class="text-sm text-base-content/60">
                            {{ $period->academic_year }} - Semester {{ $period->semester }}
                        </p>
                    </div>
                    <x-ui.badge type="success" size="sm">Aktif</x-ui.badge>
                </div>
                @endforeach
            </div>
            @endif
        </x-ui.card>

        <!-- Recent Assessments -->
        <x-ui.card>
            <h2 class="card-title mb-4">
                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Penilaian Terbaru
            </h2>

            @if($recentAssessments->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>Belum ada penilaian</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($recentAssessments as $assessment)
                <a href="{{ route('teacher.status.show', $assessment) }}"
                    class="flex items-center justify-between p-3 bg-base-200 rounded-lg hover:bg-base-300 transition-colors">
                    <div>
                        <p class="font-medium">{{ $assessment->period->name ?? '-' }}</p>
                        <p class="text-sm text-base-content/60">
                            Penilai: {{ $assessment->assessor->user->name ?? '-' }}
                        </p>
                    </div>
                    @switch($assessment->status)
                    @case('pending')
                    <x-ui.badge type="ghost" size="sm">Pending</x-ui.badge>
                    @break
                    @case('draft')
                    @case('in_progress')
                    <x-ui.badge type="warning" size="sm">Sedang Dinilai</x-ui.badge>
                    @break
                    @case('submitted')
                    <x-ui.badge type="info" size="sm">Submitted</x-ui.badge>
                    @break
                    @case('finalized')
                    <x-ui.badge type="success" size="sm">Final</x-ui.badge>
                    @break
                    @endswitch
                </a>
                @endforeach
            </div>

            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" href="{{ route('teacher.status.index') }}">
                    Lihat Semua
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </x-ui.button>
            </x-slot:actions>
            @endif
        </x-ui.card>
    </div>

    <!-- Latest Results -->
    @if($latestResults->isNotEmpty())
    <x-ui.card class="mt-6">
        <h2 class="card-title mb-4">
            <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Hasil Penilaian Terbaru
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($latestResults as $result)
            <a href="{{ route('teacher.results.show', $result) }}"
                class="p-4 bg-base-200 rounded-lg hover:bg-base-300 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium">{{ $result->period->name ?? '-' }}</span>
                    <x-ui.badge type="success" size="sm">Final</x-ui.badge>
                </div>
                <div class="text-3xl font-bold text-primary mb-1">
                    {{ number_format($result->final_score ?? 0, 2) }}
                </div>
                <p class="text-sm text-base-content/60">
                    Ranking: {{ $result->rank ?? '-' }}
                </p>
            </a>
            @endforeach
        </div>

        <x-slot:actions>
            <x-ui.button type="ghost" size="sm" href="{{ route('teacher.results.index') }}">
                Lihat Semua Hasil
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </x-ui.button>
        </x-slot:actions>
    </x-ui.card>
    @endif

</x-layouts.teacher>