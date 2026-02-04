<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Dashboard</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-base-content/60">Selamat datang di SPK Penilaian Guru</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.periods.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Periode Aktif
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Stats Cards -->
    <div class="stats stats-vertical lg:stats-horizontal shadow w-full mb-6 bg-base-100">
        <x-ui.stat title="Total Guru" :value="$totalTeachers ?? 0" description="yang terdaftar">
            <x-slot:icon>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Tim Penilai" :value="$totalAssessors ?? 0" description="yang aktif">
            <x-slot:icon>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Penilaian Selesai" :value="$completedAssessments ?? 0"
            :description="'dari ' . ($totalAssessments ?? 0) . ' total'" trend="up"
            trendValue="{{ $assessmentProgress ?? 0 }}%">
            <x-slot:icon>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Periode Aktif" :value="$activePeriod->name ?? 'Tidak Ada'"
            description="{{ $activePeriod?->status ?? '-' }}">
            <x-slot:icon>
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Progress Penilaian -->
        <x-ui.card title="Progress Penilaian" class="lg:col-span-2">
            <div class="space-y-4">
                @forelse($assessmentsByStatus ?? [] as $status => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($status === 'completed')
                        <div class="w-3 h-3 rounded-full bg-success"></div>
                        <span>Selesai</span>
                        @elseif($status === 'in_progress')
                        <div class="w-3 h-3 rounded-full bg-warning"></div>
                        <span>Sedang Dinilai</span>
                        @else
                        <div class="w-3 h-3 rounded-full bg-error"></div>
                        <span>Belum Dinilai</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-medium">{{ $count }}</span>
                        <progress class="progress w-32" value="{{ $count }}"
                            max="{{ $totalAssessments ?? 1 }}"></progress>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-base-content/60">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p>Belum ada data penilaian</p>
                </div>
                @endforelse
            </div>

            <x-slot:actions>
                <x-ui.button type="primary" size="sm" href="{{ route('admin.assessments.index') }}">
                    Lihat Detail
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>

        <!-- Quick Actions -->
        <x-ui.card title="Aksi Cepat">
            <div class="space-y-3">
                <a href="{{ route('admin.periods.create') }}" class="btn btn-outline btn-block justify-start gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buat Periode Baru
                </a>
                <a href="{{ route('admin.kpi-forms.create') }}" class="btn btn-outline btn-block justify-start gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Buat Form KPI
                </a>
                <a href="{{ route('admin.ahp.index') }}" class="btn btn-outline btn-block justify-start gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Atur Bobot AHP
                </a>
                <a href="{{ route('admin.results.index') }}" class="btn btn-outline btn-block justify-start gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Lihat Hasil Ranking
                </a>
            </div>
        </x-ui.card>
    </div>

    <!-- Recent Activities & Top Teachers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Recent Activities -->
        <x-ui.card title="Aktivitas Terbaru">
            <div class="space-y-4">
                @forelse($recentActivities ?? [] as $activity)
                <div class="flex items-start gap-3">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-10">
                            <span>{{ substr($activity->user?->name ?? 'S', 0, 1) }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm">
                            <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                            <span class="text-base-content/60">{{ $activity->description }}</span>
                        </p>
                        <p class="text-xs text-base-content/50">{{ $activity->created_at?->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-base-content/60">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Belum ada aktivitas</p>
                </div>
                @endforelse
            </div>

            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" href="{{ route('admin.activity-logs.index') }}">
                    Lihat Semua
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>

        <!-- Top 5 Teachers -->
        <x-ui.card title="Top 5 Guru (Periode Aktif)">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Nama Guru</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topTeachers ?? [] as $index => $teacher)
                        <tr>
                            <td>
                                @if($index === 0)
                                <span class="badge badge-warning badge-sm">🥇 1</span>
                                @elseif($index === 1)
                                <span class="badge badge-ghost badge-sm">🥈 2</span>
                                @elseif($index === 2)
                                <span class="badge badge-ghost badge-sm">🥉 3</span>
                                @else
                                <span class="badge badge-ghost badge-sm">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span
                                                class="text-xs">{{ substr($teacher->teacher?->user?->name ?? 'G', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <span>{{ $teacher->teacher?->user?->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="font-mono font-bold text-primary">{{ number_format($teacher->final_score ?? 0, 2) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-base-content/60">
                                Belum ada data ranking
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" href="{{ route('admin.results.index') }}">
                    Lihat Ranking Lengkap
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>
    </div>

    <!-- AHP Status Card -->
    <div class="mt-6">
        <x-ui.card title="Status Pembobotan AHP">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
                    <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Total Kriteria</p>
                        <p class="text-2xl font-bold">{{ $totalCriteria ?? 0 }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
                    <div class="w-12 h-12 rounded-full bg-success/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Consistency Ratio</p>
                        <p
                            class="text-2xl font-bold {{ ($ahpModel->consistency_ratio ?? 1) <= 0.1 ? 'text-success' : 'text-error' }}">
                            {{ number_format(($ahpModel->consistency_ratio ?? 0) * 100, 2) }}%
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
                    <div
                        class="w-12 h-12 rounded-full {{ ($ahpModel->status ?? 'draft') === 'finalized' ? 'bg-success/20' : 'bg-warning/20' }} flex items-center justify-center">
                        @if(($ahpModel->status ?? 'draft') === 'finalized')
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        @else
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-base-content/60">Status Bobot</p>
                        <p class="text-2xl font-bold">
                            @if(($ahpModel->status ?? 'draft') === 'finalized')
                            <span class="text-success">Terkunci</span>
                            @else
                            <span class="text-warning">Draft</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-ui.button type="primary" size="sm" href="{{ route('admin.ahp.index') }}">
                    Kelola Bobot AHP
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>
    </div>
</x-layouts.admin>
