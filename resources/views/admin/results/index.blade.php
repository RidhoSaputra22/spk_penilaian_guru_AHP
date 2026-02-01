<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Hasil & Ranking</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Hasil & Ranking Guru</h1>
                <p class="text-base-content/60">Lihat hasil penilaian dan ranking guru per periode</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" onclick="exportExcel()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </x-ui.button>
                <x-ui.button type="primary" onclick="exportPdf()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Export PDF
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.results.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <x-ui.select
                    name="period_id"
                    label="Periode Penilaian"
                    :options="$periods ?? []"
                    :value="$selectedPeriod->id ?? ''"
                />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select
                    name="teacher_group_id"
                    label="Kelompok Guru"
                    placeholder="Semua Kelompok"
                    :options="$teacherGroups ?? []"
                    :value="request('teacher_group_id')"
                />
            </div>
            <div class="w-full md:w-48">
                <x-ui.input
                    name="search"
                    label="Cari Guru"
                    placeholder="Nama guru..."
                    :value="request('search')"
                />
            </div>
            <div class="flex items-end gap-2">
                <x-ui.button type="primary">Filter</x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.results.index') }}">Reset</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if(isset($selectedPeriod))
        <!-- Summary Stats -->
        <div class="stats stats-vertical md:stats-horizontal shadow w-full mb-6 bg-base-100">
            <x-ui.stat title="Total Guru Dinilai" :value="$totalTeachers ?? 0">
                <x-slot:icon>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Rata-rata Skor" :value="number_format($averageScore ?? 0, 2)">
                <x-slot:icon>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Skor Tertinggi" :value="number_format($highestScore ?? 0, 2)">
                <x-slot:icon>
                    <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </x-slot:icon>
            </x-ui.stat>
            <x-ui.stat title="Skor Terendah" :value="number_format($lowestScore ?? 0, 2)">
                <x-slot:icon>
                    <svg class="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                </x-slot:icon>
            </x-ui.stat>
        </div>

        <!-- Ranking Table -->
        <x-ui.card title="Ranking Guru - {{ $selectedPeriod->name }}">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-16">Rank</th>
                            <th>Guru</th>
                            <th>Kelompok</th>
                            @foreach($criteriaHeaders ?? [] as $criteria)
                                <th class="text-center">
                                    <div class="tooltip" data-tip="{{ $criteria->name }}">
                                        {{ $criteria->code ?? Str::limit($criteria->name, 10) }}
                                    </div>
                                </th>
                            @endforeach
                            <th class="text-center">Skor Akhir</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results ?? [] as $index => $result)
                            <tr class="hover">
                                <td>
                                    @if($index === 0)
                                        <div class="badge badge-warning gap-1">
                                            🥇 1
                                        </div>
                                    @elseif($index === 1)
                                        <div class="badge badge-ghost gap-1">
                                            🥈 2
                                        </div>
                                    @elseif($index === 2)
                                        <div class="badge badge-ghost gap-1">
                                            🥉 3
                                        </div>
                                    @else
                                        <div class="badge badge-ghost">
                                            {{ $index + 1 }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                <span>{{ strtoupper(substr($result->teacher?->user?->name ?? 'G', 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $result->teacher?->user?->name ?? 'N/A' }}</div>
                                            <div class="text-sm text-base-content/60">{{ $result->teacher?->nip ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-ui.badge type="ghost" size="sm">
                                        {{ $result->teacher?->teacherGroup?->name ?? '-' }}
                                    </x-ui.badge>
                                </td>
                                @foreach($result->criteria_scores ?? [] as $score)
                                    <td class="text-center">
                                        <span class="font-mono text-sm">{{ number_format($score, 2) }}</span>
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    <span class="text-lg font-bold text-primary">{{ number_format($result->final_score ?? 0, 2) }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.results.show', $result) }}" class="btn btn-ghost btn-sm btn-circle" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.results.pdf', $result) }}" class="btn btn-ghost btn-sm btn-circle" title="Download PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + count($criteriaHeaders ?? []) }}" class="text-center py-12 text-base-content/60">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg">Belum ada hasil penilaian</p>
                                    <p class="text-sm">Penilaian mungkin belum selesai untuk periode ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($results) && $results->hasPages())
                <div class="mt-4">
                    {{ $results->links() }}
                </div>
            @endif
        </x-ui.card>

        <!-- Score Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Score Distribution Chart -->
            <x-ui.card title="Distribusi Skor">
                <div class="space-y-3">
                    @php
                        $distributions = [
                            ['range' => '90 - 100', 'count' => $scoreDistribution['90-100'] ?? 0, 'color' => 'success'],
                            ['range' => '80 - 89', 'count' => $scoreDistribution['80-89'] ?? 0, 'color' => 'info'],
                            ['range' => '70 - 79', 'count' => $scoreDistribution['70-79'] ?? 0, 'color' => 'warning'],
                            ['range' => '< 70', 'count' => $scoreDistribution['<70'] ?? 0, 'color' => 'error'],
                        ];
                        $maxCount = max(array_column($distributions, 'count')) ?: 1;
                    @endphp
                    @foreach($distributions as $dist)
                        <div class="flex items-center gap-4">
                            <span class="w-20 text-sm">{{ $dist['range'] }}</span>
                            <progress class="progress progress-{{ $dist['color'] }} flex-1" value="{{ $dist['count'] }}" max="{{ $maxCount }}"></progress>
                            <span class="w-12 text-right font-mono text-sm">{{ $dist['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <!-- Criteria Average -->
            <x-ui.card title="Rata-rata per Kriteria">
                <div class="space-y-3">
                    @foreach($criteriaAverages ?? [] as $criteria)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                            <div>
                                <div class="font-medium">{{ $criteria->name }}</div>
                                <div class="text-xs text-base-content/60">Bobot: {{ number_format($criteria->weight * 100, 1) }}%</div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold">{{ number_format($criteria->average_score, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    @else
        <x-ui.card>
            <div class="text-center py-12 text-base-content/60">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-lg">Pilih Periode</p>
                <p class="text-sm">Pilih periode penilaian untuk melihat hasil</p>
            </div>
        </x-ui.card>
    @endif

    <script>
        function exportExcel() {
            const periodId = '{{ $selectedPeriod->id ?? '' }}';
            if (periodId) {
                window.location.href = `{{ route('admin.results.export-excel') }}?period_id=${periodId}`;
            }
        }

        function exportPdf() {
            const periodId = '{{ $selectedPeriod->id ?? '' }}';
            if (periodId) {
                window.location.href = `{{ route('admin.results.export-pdf') }}?period_id=${periodId}`;
            }
        }
    </script>
</x-layouts.admin>
