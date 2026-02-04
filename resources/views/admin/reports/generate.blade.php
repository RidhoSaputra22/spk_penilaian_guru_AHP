<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.reports.index') }}">Laporan</a></li>
        <li>Generate</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold">Generate Laporan</h1>
            <p class="text-base-content/60">Buat laporan baru sesuai kebutuhan</p>
        </div>
    </x-slot:header>

    <x-ui.card title="Form Generate Laporan">
        <form method="POST" action="{{ route('admin.reports.store') }}" class="space-y-6">
            @csrf

            <!-- Report Type -->
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Jenis Laporan <span class="text-error">*</span></span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="report_type" value="results" class="peer sr-only" required>
                        <div
                            class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-primary peer-checked:bg-primary/5 hover:border-primary/50 transition-all">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Hasil & Ranking</h3>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/70">Laporan hasil penilaian lengkap dengan ranking dan
                                statistik</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="report_type" value="progress" class="peer sr-only">
                        <div
                            class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-warning peer-checked:bg-warning/5 hover:border-warning/50 transition-all">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Progress</h3>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/70">Status penilaian dan progress monitoring</p>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="report_type" value="ahp" class="peer sr-only">
                        <div
                            class="border-2 border-base-300 rounded-lg p-4 peer-checked:border-info peer-checked:bg-info/5 hover:border-info/50 transition-all">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold">Bobot AHP</h3>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/70">Pembobotan kriteria dan perhitungan AHP</p>
                        </div>
                    </label>
                </div>
                @error('report_type')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Period Selection -->
            <div>
                <x-ui.select name="period_id" label="Periode" required
                    :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->academic_year . ')'])"
                    selected="{{ old('period_id', $periods->first()?->id) }}" />
                @error('period_id')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Format Selection -->
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Format Output <span class="text-error">*</span></span>
                </label>
                <div class="flex gap-4">
                    <label
                        class="label cursor-pointer gap-3 border-2 border-base-300 rounded-lg p-4 flex-1 hover:border-success/50 has-[:checked]:border-success has-[:checked]:bg-success/5">
                        <input type="radio" name="format" value="pdf" class="radio radio-success" checked required>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="label-text font-semibold">PDF</span>
                            </div>
                            <p class="text-xs text-base-content/70">Format siap cetak</p>
                        </div>
                    </label>
                    <label
                        class="label cursor-pointer gap-3 border-2 border-base-300 rounded-lg p-4 flex-1 hover:border-info/50 has-[:checked]:border-info has-[:checked]:bg-info/5">
                        <input type="radio" name="format" value="excel" class="radio radio-info">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="label-text font-semibold">Excel</span>
                            </div>
                            <p class="text-xs text-base-content/70">Format untuk analisis data</p>
                        </div>
                    </label>
                </div>
                @error('format')
                <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">
                    <strong>Catatan:</strong> Semua jenis laporan mendukung format PDF dan Excel.
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-end pt-4 border-t">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Generate Laporan
                </button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.admin>