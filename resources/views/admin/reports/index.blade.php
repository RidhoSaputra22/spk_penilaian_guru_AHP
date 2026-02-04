<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Laporan</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Laporan</h1>
                <p class="text-base-content/60">Kelola dan unduh berbagai laporan sistem</p>
            </div>
            <a href="{{ route('admin.reports.generate') }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Generate Laporan
            </a>
        </div>
    </x-slot:header>

    <!-- Period Selection -->
    <x-ui.card class="mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <x-ui.select name="period_id" label="Pilih Periode"
                    :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->academic_year . ')'])"
                    selected="{{ request('period_id', $periods->first()?->id) }}" />
            </div>
            <x-ui.button type="primary" :isSubmit="true">
                Pilih Periode
            </x-ui.button>
        </form>
    </x-ui.card>

    @php
    $selectedPeriodId = request('period_id', $periods->first()?->id);
    @endphp

    <!-- Report Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Assessment Results Report -->
        <x-ui.card title="Laporan Hasil Penilaian" class="hover:shadow-lg transition-all">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Hasil & Ranking</h3>
                        <p class="text-sm text-base-content/70">Laporan hasil penilaian lengkap</p>
                    </div>
                </div>
                <div class="text-sm text-base-content/60 mb-4">
                    Berisi hasil akhir penilaian, ranking guru, breakdown per kriteria, dan statistik lengkap.
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.results.export', ['period_id' => $selectedPeriodId, 'format' => 'pdf']) }}"
                        target="_blank" class="btn btn-sm btn-outline btn-success flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>
                    <a href="{{ route('admin.results.export', ['period_id' => $selectedPeriodId, 'format' => 'excel']) }}"
                        target="_blank" class="btn btn-sm btn-outline btn-info flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                </div>
            </div>
        </x-ui.card>

        <!-- Assessment Progress Report -->
        <x-ui.card title="Laporan Progress Penilaian" class="hover:shadow-lg transition-all">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Progress Monitoring</h3>
                        <p class="text-sm text-base-content/70">Status penilaian guru</p>
                    </div>
                </div>
                <div class="text-sm text-base-content/60 mb-4">
                    Berisi status penilaian (pending, proses, selesai), progress keseluruhan, dan detail per penilai.
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.assessments.index', ['period' => $selectedPeriodId]) }}"
                        class="btn btn-sm btn-primary flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                </div>
            </div>
        </x-ui.card>

        <!-- AHP Weights Report -->
        <x-ui.card title="Laporan Bobot AHP" class="hover:shadow-lg transition-all">
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Pembobotan Kriteria</h3>
                        <p class="text-sm text-base-content/70">Hasil perhitungan AHP</p>
                    </div>
                </div>
                <div class="text-sm text-base-content/60 mb-4">
                    Berisi bobot setiap kriteria, consistency ratio, dan perbandingan berpasangan.
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.ahp.index', ['period' => $selectedPeriodId]) }}"
                        class="btn btn-sm btn-primary flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                </div>
            </div>
        </x-ui.card>




        <!-- Info Section -->
        <x-ui.card title="Informasi Laporan" class="mt-6">
            <div class="prose prose-sm max-w-none">
                <h4 class="font-semibold mb-2">Jenis Laporan yang Tersedia:</h4>
                <ul class="space-y-2 text-base-content/70">
                    <li>
                        <strong>Laporan Hasil & Ranking:</strong> Menampilkan hasil akhir penilaian guru lengkap dengan
                        ranking, nilai per kriteria, dan grade.
                    </li>
                    <li>
                        <strong>Laporan Progress:</strong> Memantau status penilaian yang sudah/belum dilakukan oleh
                        penilai.
                    </li>
                    <li>
                        <strong>Laporan Bobot AHP:</strong> Menampilkan bobot setiap kriteria hasil perhitungan AHP dan
                        consistency ratio.
                    </li>
                </ul>

                <h4 class="font-semibold mt-4 mb-2">Format Laporan:</h4>
                <ul class="space-y-2 text-base-content/70">
                    <li>
                        <strong>PDF:</strong> Cocok untuk cetak atau arsip formal. Tampilan sudah terformat dan siap
                        cetak.
                    </li>
                    <li>
                        <strong>Excel (CSV):</strong> Cocok untuk analisis lebih lanjut. Dapat dibuka di Excel, Google
                        Sheets, atau aplikasi spreadsheet lainnya.
                    </li>
                </ul>

                <div class="alert alert-info mt-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm">Pilih periode terlebih dahulu untuk mengakses laporan yang sesuai.</span>
                </div>
            </div>
        </x-ui.card>
    </div>

</x-layouts.admin>
