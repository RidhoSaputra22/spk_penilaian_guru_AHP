<x-layouts.assessor>
    <x-slot:title>Dashboard</x-slot:title>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $assessorProfile->title ?? 'Tim Penilai' }} - Panel Penilaian Guru
                </p>
            </div>
        </div>
    </x-slot:header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Assigned -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['total_assigned'] }}</p>
                    <p class="text-sm text-base-content/60">Total Guru Ditugaskan</p>
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
                    <p class="text-sm text-base-content/60">Belum Selesai</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Submitted -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-info">{{ $stats['submitted'] }}</p>
                    <p class="text-sm text-base-content/60">Sudah Disubmit</p>
                </div>
            </div>
        </x-ui.card>

        <!-- Finalized -->
        <x-ui.card compact>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-success">{{ $stats['finalized'] }}</p>
                    <p class="text-sm text-base-content/60">Selesai/Final</p>
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
                <p>Tidak ada periode aktif saat ini</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($activePeriods as $period)
                <a href="{{ route('assessor.assessments.period', $period) }}"
                    class="block p-4 rounded-lg border border-base-200 hover:border-primary hover:bg-primary/5 transition-colors">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold">{{ $period->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $period->academic_year }} - Semester {{ $period->semester }}
                            </p>
                        </div>
                        <x-ui.badge type="success" size="sm">Aktif</x-ui.badge>
                    </div>
                    @if($period->scoring_close_at)
                    <p class="text-xs text-base-content/50 mt-2">
                        Batas: {{ $period->scoring_close_at->format('d M Y') }}
                    </p>
                    @endif
                </a>
                @endforeach
            </div>
            @endif

            <x-slot:actions>
                <x-ui.button type="primary" size="sm" href="{{ route('assessor.assessments.index') }}">
                    Lihat Semua Periode
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>

        <!-- Pending Assessments -->
        <x-ui.card>
            <h2 class="card-title mb-4">
                <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Penilaian Menunggu
            </h2>

            @if($pendingAssessments->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Semua penilaian sudah selesai!</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Guru</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAssessments as $assessment)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $assessment->teacher->user->name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">{{ $assessment->teacher->employee_no ?? '' }}
                                </div>
                            </td>
                            <td class="text-sm">{{ $assessment->period->name ?? '-' }}</td>
                            <td>
                                @if($assessment->status === 'draft')
                                <x-ui.badge type="warning" size="xs">Draft</x-ui.badge>
                                @else
                                <x-ui.badge type="ghost" size="xs">Pending</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                <x-ui.button type="primary" size="xs"
                                    href="{{ route('assessor.assessments.score', [$assessment->period, $assessment->teacher]) }}">
                                    Lanjutkan
                                </x-ui.button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Recent Submitted -->
    @if($recentSubmitted->isNotEmpty())
    <x-ui.card class="mt-6" title="Penilaian Terbaru">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Periode</th>
                        <th>Tanggal Submit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentSubmitted as $assessment)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $assessment->teacher->user->name ?? '-' }}</div>
                        </td>
                        <td>{{ $assessment->period->name ?? '-' }}</td>
                        <td>{{ $assessment->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td>
                            <x-ui.badge type="success" size="sm">Submitted</x-ui.badge>
                        </td>
                        <td>
                            <x-ui.button type="ghost" size="xs"
                                href="{{ route('assessor.results.show', $assessment) }}">
                                Lihat
                            </x-ui.button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>
    @endif

</x-layouts.assessor>