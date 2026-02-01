@extends('layouts.admin')

@section('title', 'Detail Hasil Penilaian')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Detail Hasil Penilaian</h1>
            <p class="text-base-content/70">{{ $result->teacher?->user?->name ?? '-' }} - {{ $result->periodResult?->period?->name ?? '' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.results.index') }}" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <a href="{{ route('admin.results.export', ['period' => $result->periodResult?->period?->id]) }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Final Score Card -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base-content/70">Skor Akhir</h3>
                            <div class="text-5xl font-bold text-primary mt-2">{{ number_format($result->final_score ?? 0, 2) }}</div>
                            <p class="text-sm text-base-content/70 mt-1">Ranking #{{ $result->rank ?? '-' }}</p>
                        </div>
                        <div class="text-center">
                            @php
                                $gradeColors = [
                                    'A' => 'success',
                                    'B' => 'info',
                                    'C' => 'warning',
                                    'D' => 'error',
                                    'E' => 'neutral',
                                ];
                                $gradeLabels = [
                                    'A' => 'Sangat Baik',
                                    'B' => 'Baik',
                                    'C' => 'Cukup',
                                    'D' => 'Kurang',
                                    'E' => 'Sangat Kurang',
                                ];
                                $color = $gradeColors[$result->grade] ?? 'neutral';
                                $label = $gradeLabels[$result->grade] ?? '-';
                            @endphp
                            <div class="radial-progress text-{{ $color }}" style="--value:{{ min($result->final_score ?? 0, 100) }}; --size:5rem; --thickness: 0.5rem;" role="progressbar">
                                <span class="text-2xl font-bold">{{ $result->grade }}</span>
                            </div>
                            <p class="text-sm text-base-content/70 mt-2">{{ $label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Breakdown -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Rincian Skor per Kriteria</h3>
                    <p class="text-sm text-base-content/70 mb-4">Skor berdasarkan kriteria penilaian dengan bobot AHP</p>

                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kriteria</th>
                                    <th class="text-center">Skor Mentah</th>
                                    <th class="text-center">Bobot (%)</th>
                                    <th class="text-center">Skor Tertimbang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rootCriteria as $item)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $item['criterion']->name }}</div>
                                        <div class="text-sm text-base-content/50">{{ $item['criterion']->code }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-mono">{{ number_format($item['raw_score'], 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-ghost">{{ number_format($item['weight'], 1) }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="font-mono font-medium text-primary">{{ number_format($item['weighted_score'], 4) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-base-200">
                                    <th>Total</th>
                                    <th></th>
                                    <th class="text-center">100%</th>
                                    <th class="text-center">
                                        <span class="font-mono font-bold text-primary">{{ number_format($result->final_score ?? 0, 4) }}</span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Visual Score Chart -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Visualisasi Skor</h3>

                    <div class="space-y-4 mt-4">
                        @foreach($rootCriteria as $item)
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">{{ $item['criterion']->name }}</span>
                                <span class="text-sm text-base-content/70">{{ number_format($item['raw_score'], 2) }} / 100</span>
                            </div>
                            <progress class="progress progress-primary w-full" value="{{ $item['raw_score'] }}" max="100"></progress>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Teacher Info -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Informasi Guru</h3>

                    <div class="flex items-center gap-4 mt-4">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-16">
                                <span class="text-xl">{{ substr($result->teacher?->user?->name ?? '', 0, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold">{{ $result->teacher?->user?->name ?? '-' }}</h4>
                            <p class="text-sm text-base-content/70">{{ $result->teacher?->user?->email ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">NIP</span>
                            <span class="font-medium">{{ $result->teacher?->nip ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">NUPTK</span>
                            <span class="font-medium">{{ $result->teacher?->nuptk ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Kelompok</span>
                            <span class="font-medium">{{ $result->teacher?->teacherGroup?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Jabatan</span>
                            <span class="font-medium">{{ $result->teacher?->position ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Period Info -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Periode Penilaian</h3>

                    <div class="space-y-3 text-sm mt-4">
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Periode</span>
                            <span class="font-medium">{{ $result->periodResult?->period?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Tanggal Mulai</span>
                            <span class="font-medium">{{ $result->periodResult?->period?->start_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Tanggal Selesai</span>
                            <span class="font-medium">{{ $result->periodResult?->period?->end_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Dihitung Pada</span>
                            <span class="font-medium">{{ $result->created_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historical Results -->
            @if($historicalResults->count() > 0)
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Riwayat Penilaian</h3>

                    <div class="space-y-3 mt-4">
                        @foreach($historicalResults as $history)
                        @php
                            $historyGrade = match(true) {
                                ($history->final_score ?? 0) >= 90 => 'A',
                                ($history->final_score ?? 0) >= 80 => 'B',
                                ($history->final_score ?? 0) >= 70 => 'C',
                                ($history->final_score ?? 0) >= 60 => 'D',
                                default => 'E',
                            };
                            $historyColor = $gradeColors[$historyGrade] ?? 'neutral';
                        @endphp
                        <a href="{{ route('admin.results.show', $history) }}" class="flex items-center justify-between p-3 rounded-lg bg-base-200/50 hover:bg-base-200 transition-colors">
                            <div>
                                <p class="font-medium text-sm">{{ $history->periodResult?->period?->name ?? '-' }}</p>
                                <p class="text-xs text-base-content/50">{{ $history->created_at?->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $historyColor }}">{{ $historyGrade }}</span>
                                <p class="text-sm font-mono mt-1">{{ number_format($history->final_score ?? 0, 2) }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
