<x-layouts.teacher>
    <x-slot:title>Detail Hasil - {{ $period->name ?? 'Penilaian' }}</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('teacher.results.index') }}">Hasil Penilaian</a></li>
        <li>Detail</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $period->name ?? '' }} - {{ $period->academic_year ?? '' }}
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download PDF
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card compact class="text-center">
            <div class="radial-progress text-primary mx-auto"
                style="--value:{{ min(100, $result->final_score ?? 0) }}; --size:6rem; --thickness:0.4rem;"
                role="progressbar">
                <span class="text-xl font-bold">{{ number_format($result->final_score ?? 0, 1) }}</span>
            </div>
            <p class="mt-2 text-sm text-base-content/60">Skor Akhir</p>
        </x-ui.card>

        <x-ui.card compact class="text-center">
            <div class="text-4xl font-bold text-secondary">{{ $result->rank ?? '-' }}</div>
            <p class="text-sm text-base-content/60 mt-2">Ranking</p>
            @if($totalTeachers > 0)
            <p class="text-xs text-base-content/40">dari {{ $totalTeachers }} guru</p>
            @endif
        </x-ui.card>

        <x-ui.card compact class="text-center">
            @php
            $gradeColor = match($grade) {
            'A' => 'text-success',
            'B' => 'text-info',
            'C' => 'text-warning',
            'D' => 'text-error',
            default => 'text-base-content',
            };
            @endphp
            <div class="text-4xl font-bold {{ $gradeColor }}">{{ $grade }}</div>
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
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p>Detail breakdown kriteria tidak tersedia.</p>
            </div>
            @else
            <div class="space-y-4 grid grid-cols-3 gap-5">
                @foreach($criteriaScores as $data)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-sm">{{ $data['name'] }}</span>
                        <span class="text-sm font-semibold">{{ number_format($data['raw_score'], 2) }}</span>
                    </div>
                    @php
                    $maxScore = 100;
                    $percentage = $maxScore > 0 ? min(100, ($data['raw_score'] / $maxScore) * 100) : 0;
                    @endphp
                    <progress class="progress progress-primary w-full" value="{{ $percentage }}" max="100"></progress>
                    <div class="flex  mt-1">
                        <p class="text-xs text-base-content/60 text-start">
                            Bobot: {{ number_format($data['weight'] * 100, 1) }}%
                        </p>
                        <p class="text-xs text-base-content/60 text-end">
                            Skor terbobot: {{ number_format($data['weighted_score'], 2) }}
                        </p>
                    </div>
                </div>
                @endforeach

                {{-- Total --}}
                <div class="border-t border-base-300 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold">Total Skor Akhir</span>
                        <span
                            class="font-bold text-primary text-lg">{{ number_format($result->final_score ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </x-ui.card>

        <!-- Assessors List -->
        <x-ui.card title="Daftar Penilai">
            @if($assessments->isEmpty())
            <div class="text-center py-8 text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p>Tidak ada data penilai.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($assessments as $assessment)
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div
                                class="bg-secondary text-secondary-content rounded-full w-10 h-10 flex justify-center items-center">
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
                    <div class="text-right">
                        @if($assessment->status === 'finalized')
                        <x-ui.badge type="success" size="sm">Selesai</x-ui.badge>
                        @elseif($assessment->status === 'submitted')
                        <x-ui.badge type="info" size="sm">Disubmit</x-ui.badge>
                        @else
                        <x-ui.badge type="warning" size="sm">{{ ucfirst($assessment->status) }}</x-ui.badge>
                        @endif
                        @if($assessment->submitted_at)
                        <p class="text-xs text-base-content/50 mt-1">{{ $assessment->submitted_at->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Score Chart (CSS-based bar chart, no JS) -->
    @if(!empty($criteriaScores))
    <x-ui.card title="Grafik Skor per Kriteria" class="mt-6">
        <div class="space-y-3">
            @php
            $maxRawScore = max(array_column($criteriaScores, 'raw_score'));
            $maxRawScore = $maxRawScore > 0 ? $maxRawScore : 1;
            @endphp
            @foreach($criteriaScores as $data)
            @php
            $barPercent = ($data['raw_score'] / $maxRawScore) * 100;
            $barColor = $barPercent >= 80 ? 'bg-success' : ($barPercent >= 60 ? 'bg-info' : ($barPercent >= 40 ?
            'bg-warning' : 'bg-error'));
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-28 text-xs font-medium text-right shrink-0 truncate" title="{{ $data['name'] }}">
                    {{ $data['code'] ?: $data['name'] }}
                </div>
                <div class="flex-1 bg-base-200 rounded-full h-6 relative overflow-hidden">
                    <div class="{{ $barColor }} h-full rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                        style="width: {{ max(5, $barPercent) }}%;">
                        <span
                            class="text-xs font-bold text-white drop-shadow">{{ number_format($data['raw_score'], 1) }}</span>
                    </div>
                </div>
                <div class="w-16 text-xs text-base-content/60 shrink-0">
                    {{ number_format($data['weight'] * 100, 1) }}%
                </div>
            </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-4 mt-4 pt-3 border-t border-base-200 text-xs text-base-content/60">
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-success"></div>
                <span>≥ 80%</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-info"></div>
                <span>60-79%</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-warning"></div>
                <span>40-59%</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-error"></div>
                <span>&lt; 40%</span>
            </div>
        </div>
    </x-ui.card>
    @else
    <x-ui.card title="Grafik Skor per Kriteria" class="mt-6">
        <div class="h-40 flex items-center justify-center">
            <div class="text-center text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p>Data grafik belum tersedia</p>
            </div>
        </div>
    </x-ui.card>
    @endif

    <!-- Period Info -->
    <x-ui.card title="Informasi Periode" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-base-content/60">Nama Periode</p>
                <p class="font-medium">{{ $period->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Tahun Akademik</p>
                <p class="font-medium">{{ $period->academic_year ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Semester</p>
                <p class="font-medium">{{ $period->semester ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Tanggal Hasil Dihitung</p>
                <p class="font-medium">
                    {{ $result->periodResult?->generated_at?->format('d M Y H:i') ?? $result->created_at?->format('d M Y H:i') ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Total Guru Dinilai</p>
                <p class="font-medium">{{ $totalTeachers }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Metode Perhitungan</p>
                <p class="font-medium">AHP (Analytical Hierarchy Process)</p>
            </div>
        </div>
    </x-ui.card>

</x-layouts.teacher>
