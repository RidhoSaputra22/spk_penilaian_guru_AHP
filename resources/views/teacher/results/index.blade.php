<x-layouts.teacher>
    <x-slot:title>Hasil Penilaian</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Hasil Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">Lihat hasil penilaian kinerja Anda</p>
            </div>
        </div>
    </x-slot:header>

    <!-- Filter -->
    <x-ui.card class="mb-6" compact>
        <form action="{{ route('teacher.results.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <x-ui.select
                    name="period_id"
                    label="Filter Periode"
                    :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->academic_year . ')'])->toArray()"
                    placeholder="Semua Periode"
                    :selected="request('period_id')"
                    onchange="this.form.submit()"
                />
            </div>
            <div>
                <x-ui.button type="ghost" href="{{ route('teacher.results.index') }}">
                    Reset Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($results->isEmpty())
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Belum Ada Hasil Penilaian</h3>

                @if(isset($assessmentStatus))
                    @if($assessmentStatus['total'] == 0)
                        <p class="text-base-content/60 mb-4">Anda belum memiliki penilaian dari assessor.</p>

                        @if($assessmentStatus['has_active_period'])
                            <x-ui.alert type="info" class="max-w-md mx-auto">
                                <p class="text-sm">
                                    <strong>Periode penilaian sedang aktif.</strong><br>
                                    Silakan tunggu assessor menyelesaikan penilaian terhadap kinerja Anda.
                                </p>
                            </x-ui.alert>
                        @else
                            <x-ui.alert type="warning" class="max-w-md mx-auto">
                                <p class="text-sm">
                                    Belum ada periode penilaian yang aktif.<br>
                                    Hubungi admin untuk informasi lebih lanjut.
                                </p>
                            </x-ui.alert>
                        @endif
                    @elseif($assessmentStatus['submitted'] == 0)
                        <p class="text-base-content/60 mb-4">Penilaian Anda sedang dalam proses.</p>
                        <x-ui.alert type="info" class="max-w-md mx-auto">
                            <div class="text-sm">
                                <strong>Status Penilaian:</strong>
                                <ul class="mt-2 space-y-1">
                                    <li>• Total penilaian: {{ $assessmentStatus['total'] }}</li>
                                    <li>• Masih draft: {{ $assessmentStatus['draft'] }}</li>
                                    <li>• Selesai: {{ $assessmentStatus['submitted'] }}</li>
                                </ul>
                                <p class="mt-3">Hasil akan muncul setelah assessor menyelesaikan penilaian dan admin menghitung hasil.</p>
                            </div>
                        </x-ui.alert>
                    @else
                        <p class="text-base-content/60 mb-4">Penilaian Anda sudah selesai, menunggu perhitungan hasil.</p>
                        <x-ui.alert type="success" class="max-w-md mx-auto">
                            <div class="text-sm">
                                <strong>Status Penilaian:</strong>
                                <ul class="mt-2 space-y-1">
                                    <li>• Total penilaian selesai: {{ $assessmentStatus['submitted'] }}</li>
                                </ul>
                                <p class="mt-3">Silakan tunggu admin menghitung dan mempublikasikan hasil akhir.</p>
                            </div>
                        </x-ui.alert>
                    @endif
                @else
                    <p class="text-base-content/60">Hasil penilaian Anda belum tersedia.</p>
                @endif
            </div>
        </x-ui.card>
    @else
        <!-- Results Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($results as $result)
                @php
                    $period = $result->periodResult?->period;
                @endphp
                <x-ui.card class="hover:shadow-2xl transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-bold">{{ $period->name ?? '-' }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $period->academic_year ?? '' }} - {{ $period->semester ?? '' }}
                            </p>
                        </div>
                        <x-ui.badge type="success" size="sm">Final</x-ui.badge>
                    </div>

                    <div class="text-center py-4">
                        <div class="radial-progress text-primary" style="--value:{{ min(100, $result->final_score ?? 0) }}; --size:8rem; --thickness:0.5rem;" role="progressbar">
                            <span class="text-2xl font-bold">{{ number_format($result->final_score ?? 0, 1) }}</span>
                        </div>
                        <p class="mt-2 text-sm text-base-content/60">Skor Akhir</p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center border-t border-base-200 pt-4">
                        <div>
                            <p class="text-2xl font-bold text-secondary">{{ $result->rank ?? '-' }}</p>
                            <p class="text-xs text-base-content/60">Ranking</p>
                            @if($result->total_teachers)
                                <p class="text-xs text-base-content/40">/ {{ $result->total_teachers }}</p>
                            @endif
                        </div>
                        <div>
                            @php
                                $gradeColor = match($result->grade ?? '') {
                                    'A' => 'text-success',
                                    'B' => 'text-info',
                                    'C' => 'text-warning',
                                    'D' => 'text-error',
                                    default => 'text-base-content',
                                };
                            @endphp
                            <p class="text-2xl font-bold {{ $gradeColor }}">{{ $result->grade ?? '-' }}</p>
                            <p class="text-xs text-base-content/60">Grade</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ $result->total_teachers ?? '-' }}</p>
                            <p class="text-xs text-base-content/60">Total Guru</p>
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-ui.button type="primary" href="{{ route('teacher.results.show', $result) }}">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Detail
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($results->hasPages())
            <div class="mt-6">
                {{ $results->withQueryString()->links() }}
            </div>
        @endif
    @endif

</x-layouts.teacher>
