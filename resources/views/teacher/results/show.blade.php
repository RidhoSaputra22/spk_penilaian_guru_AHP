<x-layouts.teacher>
    <x-slot:title>Detail Hasil - {{ $result->period->name ?? 'Penilaian' }}</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('teacher.results.index') }}">Hasil Penilaian</a></li>
        <li>Detail</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $result->period->name ?? '' }} - {{ $result->period->academic_year ?? '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('teacher.results.index') }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </x-ui.button>
                <x-ui.button type="primary" href="{{ route('teacher.results.download', $result) }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download PDF
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card compact class="text-center">
            <div class="radial-progress text-primary mx-auto" style="--value:{{ min(100, $result->final_score ?? 0) }}; --size:6rem; --thickness:0.4rem;" role="progressbar">
                <span class="text-xl font-bold">{{ number_format($result->final_score ?? 0, 1) }}</span>
            </div>
            <p class="mt-2 text-sm text-base-content/60">Skor Akhir</p>
        </x-ui.card>

        <x-ui.card compact class="text-center">
            <div class="text-4xl font-bold text-secondary">{{ $result->rank ?? '-' }}</div>
            <p class="text-sm text-base-content/60 mt-2">Ranking</p>
        </x-ui.card>

        <x-ui.card compact class="text-center">
            <div class="text-4xl font-bold text-accent">{{ $result->grade ?? '-' }}</div>
            <p class="text-sm text-base-content/60 mt-2">Grade</p>
        </x-ui.card>

        <x-ui.card compact class="text-center">
            <div class="text-4xl font-bold">{{ $assessments->count() }}</div>
            <p class="text-sm text-base-content/60 mt-2">Jumlah Penilai</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Criteria Breakdown -->
        <x-ui.card title="Breakdown per Kriteria">
            @if(empty($criteriaScores))
                <div class="text-center py-8 text-base-content/60">
                    <p>Detail breakdown kriteria tidak tersedia.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($criteriaScores as $criteria => $data)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-medium">{{ $criteria }}</span>
                                <span class="text-sm">{{ number_format($data['score'] ?? 0, 2) }}</span>
                            </div>
                            <progress class="progress progress-primary w-full" value="{{ $data['score'] ?? 0 }}" max="100"></progress>
                            @if(isset($data['weight']))
                                <p class="text-xs text-base-content/60 mt-1">
                                    Bobot: {{ number_format($data['weight'] * 100, 1) }}%
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <!-- Assessors List -->
        <x-ui.card title="Daftar Penilai">
            @if($assessments->isEmpty())
                <div class="text-center py-8 text-base-content/60">
                    <p>Tidak ada data penilai.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($assessments as $assessment)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-secondary text-secondary-content rounded-full w-10">
                                        <span>{{ substr($assessment->assessor->user->name ?? '?', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $assessment->assessor->user->name ?? '-' }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $assessment->assessor->title ?? 'Tim Penilai' }}
                                    </p>
                                </div>
                            </div>
                            <x-ui.badge type="success" size="sm">Selesai</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Score Chart (placeholder for chart.js) -->
    <x-ui.card title="Grafik Skor per Kriteria" class="mt-6">
        <div class="h-64 flex items-center justify-center bg-base-200 rounded-lg">
            <div class="text-center text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p>Grafik akan ditampilkan di sini</p>
            </div>
        </div>
    </x-ui.card>

    <!-- Period Info -->
    <x-ui.card title="Informasi Periode" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-base-content/60">Nama Periode</p>
                <p class="font-medium">{{ $result->period->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Tahun Akademik</p>
                <p class="font-medium">{{ $result->period->academic_year ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Semester</p>
                <p class="font-medium">{{ $result->period->semester ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Tanggal Hasil Dihitung</p>
                <p class="font-medium">{{ $result->calculated_at?->format('d M Y H:i') ?? $result->created_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Total Guru Dinilai</p>
                <p class="font-medium">{{ $result->total_teachers ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Metode Perhitungan</p>
                <p class="font-medium">AHP (Analytical Hierarchy Process)</p>
            </div>
        </div>
    </x-ui.card>

</x-layouts.teacher>
