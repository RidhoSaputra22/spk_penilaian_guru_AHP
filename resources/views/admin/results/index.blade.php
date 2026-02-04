<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Hasil & Ranking</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Hasil & Ranking</h1>
                <p class="text-base-content/60">Lihat hasil akhir penilaian dan peringkat guru</p>
            </div>
            @if($selectedPeriod && $results->isNotEmpty())
            <div class="flex gap-2">
                <x-ui.button type="success"
                    onclick="window.open('{{ route('admin.results.export', ['period_id' => $selectedPeriod->id, 'format' => 'pdf']) }}', '_blank')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export PDF
                </x-ui.button>
                <x-ui.button type="info"
                    onclick="window.open('{{ route('admin.results.export', ['period_id' => $selectedPeriod->id, 'format' => 'excel']) }}', '_blank')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </x-ui.button>
            </div>
            @endif
        </div>
    </x-slot:header>

    <!-- Period & Filter Selection -->
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.results.index') }}" class="flex  gap-4">
            <!-- Period -->
            <div class="flex-1">
                <x-ui.select name="period_id" label="Periode Penilaian"
                    :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->academic_year . ')'])"
                    selected="{{ $selectedPeriod?->id }}" onchange="this.form.submit()" />
            </div>

            <!-- Search -->
            <div class="flex-1">

                <x-ui.input type="text" label="Cari Guru" name="search" placeholder="Nama guru..."
                    value="{{ request('search') }}" class="w-64" />
            </div>

            <!-- Grade Filter -->
            <div class="flex-1">
                <x-ui.select name="grade" label="Grade" :options="[
                        '' => 'Semua Grade',
                        'A' => 'A (90-100)',
                        'B' => 'B (80-89)',
                        'C' => 'C (70-79)',
                        'D' => 'D (60-69)',
                        'E' => 'E (<60)',
                    ]" selected="{{ request('grade') }}" />
            </div>



            <!-- Submit Button -->
            <div class="flex items-end">
                <x-ui.button type="primary" :isSubmit="true" class="w-full">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($selectedPeriod)
    @if($results->isNotEmpty())
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-ui.stat title="Total Guru" value="{{ $results->total() }}" icon="users" variant="primary" />
        <x-ui.stat title="Rata-rata Nilai" :value="number_format($statsData['avg_score'] ?? 0, 2)" icon="chart"
            variant="info" />
        <x-ui.stat title="Nilai Tertinggi" :value="number_format($statsData['max_score'] ?? 0, 2)" icon="trophy"
            variant="success" />
        <x-ui.stat title="Nilai Terendah" :value="number_format($statsData['min_score'] ?? 0, 2)" icon="warning"
            variant="warning" />
    </div>

    <!-- Criteria Averages -->
    @if($criteria->isNotEmpty())
    <x-ui.card title="Rata-rata per Kriteria" class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($criteria as $criterion)
            <div class="stat bg-base-200 rounded-lg">
                <div class="stat-title">{{ $criterion->name }}</div>
                <div class="stat-value text-lg">{{ $criteriaAverages[$criterion->id] ?? 0 }}</div>
                <progress class="progress progress-primary w-full mt-2"
                    value="{{ $criteriaAverages[$criterion->id] ?? 0 }}" max="100"></progress>
            </div>
            @endforeach
        </div>
    </x-ui.card>
    @endif

    <!-- Results Table -->
    <x-ui.card title="Peringkat Guru">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th class="w-16">Rank</th>
                        <th>Nama Guru</th>
                        <th>NIP</th>
                        @foreach($criteria as $criterion)
                        <th class="text-center">{{ $criterion->code }}</th>
                        @endforeach
                        <th class="text-center">Nilai Akhir</th>
                        <th class="text-center">Grade</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($result->rank <= 3) <div
                                    class="badge {{ $result->rank == 1 ? 'badge-warning' : ($result->rank == 2 ? 'badge-neutral' : 'badge-accent') }}">
                                    #{{ $result->rank }}
                            </div>
                            @else
                            <span class="font-semibold">#{{ $result->rank }}</span>
                            @endif
        </div>
        </td>
        <td>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-primary/10 text-primary rounded-full w-10 flex items-center justify-center">
                        <span class="text-sm">{{ substr($result->teacher->user->name ?? '', 0, 2) }}</span>
                    </div>
                </div>
                <div class="font-medium">{{ $result->teacher->user->name ?? '-' }}</div>
            </div>
        </td>
        <td class="text-sm text-base-content/70">{{ $result->teacher->nip ?? '-' }}</td>
        @foreach($criteria as $criterion)
        <td class="text-center">
            @php
            $criteriaScore = $result->criteriaScores->firstWhere('criteria_node_id', $criterion->id);
            @endphp
            <span class="badge badge-ghost">
                {{ number_format($criteriaScore->weighted_score ?? 0, 2) }}
            </span>
        </td>
        @endforeach
        <td class="text-center">
            <span class="font-bold text-primary text-lg">
                {{ number_format($result->final_score, 2) }}
            </span>
        </td>
        <td class="text-center">
            <span class="badge {{
                                        $result->grade == 'A' ? 'badge-success' :
                                        ($result->grade == 'B' ? 'badge-info' :
                                        ($result->grade == 'C' ? 'badge-warning' : 'badge-error'))
                                    }} badge-lg">
                {{ $result->grade }}
            </span>
        </td>
        <td class="text-right">
            <a href="{{ route('admin.results.show', $result) }}" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Detail
            </a>
        </td>
        </tr>

        @endforeach
        </tbody>
        </table>
        </div>

        <!-- Pagination -->
        @if($results->hasPages())
        <div class="mt-4 flex justify-end">
            {{ $results->links() }}
        </div>
        @endif
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-base-content/30" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="text-lg font-semibold mb-2">Belum Ada Hasil Penilaian</h3>
            <p class="text-base-content/60 mb-4">Hasil penilaian akan muncul setelah perhitungan dilakukan</p>
        </div>
    </x-ui.card>
    @endif
    @else
    <x-ui.card>
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto mb-4 text-base-content/30" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="text-lg font-semibold mb-2">Pilih Periode</h3>
            <p class="text-base-content/60">Pilih periode penilaian untuk melihat hasil</p>
        </div>
    </x-ui.card>
    @endif
</x-layouts.admin>
