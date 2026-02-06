<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.results.index') }}">Hasil & Ranking</a></li>
        <li>Detail</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $result->teacher->user->name ?? '-' }} — {{ $period->name ?? '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.results.index', ['period_id' => $period?->id]) }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Teacher Info + Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
        {{-- Teacher Profile --}}
        <x-ui.card compact>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-14 h-14 flex items-center justify-center">
                        <span class="text-xl font-bold">{{ substr($result->teacher->user->name ?? '?', 0, 2) }}</span>
                    </div>
                </div>
                <div>
                    <p class="font-bold text-lg">{{ $result->teacher->user->name ?? '-' }}</p>
                    <p class="text-sm text-base-content/60">{{ $result->teacher->employee_no ?? '-' }}</p>
                    @if($result->teacher->subject)
                        <span class="badge badge-ghost badge-sm mt-1">{{ $result->teacher->subject }}</span>
                    @endif
                </div>
            </div>
        </x-ui.card>

        {{-- Score --}}
        <x-ui.card compact class="text-center">
            <div class="radial-progress text-primary mx-auto"
                style="--value:{{ min(100, $result->final_score ?? 0) }}; --size:5rem; --thickness:0.4rem;"
                role="progressbar">
                <span class="text-lg font-bold">{{ number_format($result->final_score ?? 0, 1) }}</span>
            </div>
            <p class="mt-2 text-sm text-base-content/60">Skor Akhir</p>
        </x-ui.card>

        {{-- Rank --}}
        <x-ui.card compact class="text-center">
            <div class="text-3xl font-bold text-secondary">{{ $result->rank ?? '-' }}</div>
            <p class="text-sm text-base-content/60 mt-1">Ranking</p>
            @if($totalTeachers > 0)
                <p class="text-xs text-base-content/40">dari {{ $totalTeachers }} guru</p>
            @endif
        </x-ui.card>

        {{-- Grade --}}
        <x-ui.card compact class="text-center">
            @php
                $gradeColor = match($grade) {
                    'A' => 'text-success',
                    'B' => 'text-info',
                    'C' => 'text-warning',
                    'D', 'E' => 'text-error',
                    default => 'text-base-content',
                };
            @endphp
            <div class="text-3xl font-bold {{ $gradeColor }}">{{ $grade }}</div>
            <p class="text-sm text-base-content/60 mt-1">Grade</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Criteria Breakdown Table -->
        <x-ui.card title="Breakdown per Kriteria">
            @if($criteriaScores->isEmpty())
                <div class="text-center py-8 text-base-content/60">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Belum ada data kriteria.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Kriteria</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Skor</th>
                                <th class="text-center">Skor Terbobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criteriaScores as $data)
                                <tr>
                                    <td>
                                        <span class="font-medium">{{ $data['name'] }}</span>
                                        @if($data['code'])
                                            <span class="text-xs text-base-content/50 ml-1">({{ $data['code'] }})</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-ghost badge-sm">{{ number_format($data['weight'] * 100, 1) }}%</span>
                                    </td>
                                    <td class="text-center">{{ number_format($data['raw_score'], 2) }}</td>
                                    <td class="text-center font-semibold">{{ number_format($data['weighted_score'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold">
                                <td colspan="3" class="text-right">Total Skor Akhir</td>
                                <td class="text-center text-primary text-lg">{{ number_format($result->final_score ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Visual Bar Chart --}}
                <div class="mt-4 space-y-2">
                    @php
                        $maxRawScore = $criteriaScores->max('raw_score');
                        $maxRawScore = $maxRawScore > 0 ? $maxRawScore : 1;
                    @endphp
                    @foreach($criteriaScores as $data)
                        @php
                            $barPercent = ($data['raw_score'] / $maxRawScore) * 100;
                            $barColor = $barPercent >= 80 ? 'bg-success' : ($barPercent >= 60 ? 'bg-info' : ($barPercent >= 40 ? 'bg-warning' : 'bg-error'));
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-20 text-xs font-medium text-right shrink-0 truncate" title="{{ $data['name'] }}">
                                {{ $data['code'] ?: $data['name'] }}
                            </div>
                            <div class="flex-1 bg-base-200 rounded-full h-4 overflow-hidden">
                                <div class="{{ $barColor }} h-full rounded-full" style="width: {{ max(5, $barPercent) }}%;"></div>
                            </div>
                            <div class="w-12 text-xs text-right shrink-0">{{ number_format($data['raw_score'], 1) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <!-- Right Column: Assessors + Period Info -->
        <div class="space-y-6">
            {{-- Assessor List --}}
            <x-ui.card title="Daftar Penilai">
                @if($assessments->isEmpty())
                    <div class="text-center py-6 text-base-content/60">
                        <p>Tidak ada data penilai.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($assessments as $assessment)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-secondary text-secondary-content rounded-full w-9 h-9 flex justify-center items-center">
                                            <span class="text-sm">{{ substr($assessment->assessor->user->name ?? '?', 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ $assessment->assessor->user->name ?? '-' }}</p>
                                        <p class="text-xs text-base-content/60">{{ $assessment->assessor->title ?? 'Tim Penilai' }}</p>
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
                                        <p class="text-xs text-base-content/50 mt-0.5">{{ $assessment->submitted_at->format('d M Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- Period Info --}}
            <x-ui.card title="Informasi Periode">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-base-content/60">Nama Periode</span>
                        <span class="font-medium text-sm">{{ $period->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-base-content/60">Tahun Akademik</span>
                        <span class="font-medium text-sm">{{ $period->academic_year ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-base-content/60">Semester</span>
                        <span class="font-medium text-sm">{{ $period->semester ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-base-content/60">Tanggal Hitung</span>
                        <span class="font-medium text-sm">{{ $result->periodResult?->generated_at?->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-base-content/60">Metode</span>
                        <span class="font-medium text-sm">AHP</span>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Historical Results -->
    @if($historicalResults->count() > 1)
        <x-ui.card title="Riwayat Penilaian" class="mb-6">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th class="text-center">Skor Akhir</th>
                            <th class="text-center">Ranking</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historicalResults as $history)
                            @php
                                $historyPeriod = $history->periodResult?->period;
                                $isCurrent = $history->id === $result->id;
                            @endphp
                            <tr class="{{ $isCurrent ? 'bg-primary/5 font-semibold' : '' }}">
                                <td>
                                    {{ $historyPeriod->name ?? '-' }}
                                    @if($isCurrent)
                                        <span class="badge badge-primary badge-xs ml-1">Saat ini</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($history->final_score, 2) }}</td>
                                <td class="text-center">{{ $history->rank ?? '-' }}</td>
                                <td class="text-center">
                                    @php
                                        $hGradeColor = match($history->grade ?? '') {
                                            'A' => 'badge-success',
                                            'B' => 'badge-info',
                                            'C' => 'badge-warning',
                                            default => 'badge-error',
                                        };
                                    @endphp
                                    <span class="badge {{ $hGradeColor }} badge-sm">{{ $history->grade ?? '-' }}</span>
                                </td>
                                <td class="text-center text-sm text-base-content/60">
                                    {{ $history->created_at?->format('d M Y') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

</x-layouts.admin>
