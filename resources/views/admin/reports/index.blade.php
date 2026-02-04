<x-layouts.admin>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Laporan</h1>
                <p class="text-base-content/70 mt-1">Kelola dan unduh berbagai laporan sistem</p>
            </div>
            <x-ui.button variant="primary" size="sm" onclick="generateReportModal.showModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Generate Laporan
            </x-ui.button>
        </div>

        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Assessment Reports -->
            <x-ui.card title="Laporan Penilaian" class="hover:shadow-lg transition-all cursor-pointer">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Penilaian Guru</h3>
                            <p class="text-sm text-base-content/70">Laporan hasil penilaian</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('assessment', 'pdf')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PDF
                        </x-ui.button>
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('assessment', 'excel')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <!-- Ranking Reports -->
            <x-ui.card title="Laporan Ranking" class="hover:shadow-lg transition-all cursor-pointer">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Ranking AHP</h3>
                            <p class="text-sm text-base-content/70">Peringkat berdasarkan AHP</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('ranking', 'pdf')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PDF
                        </x-ui.button>
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('ranking', 'excel')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <!-- Activity Reports -->
            <x-ui.card title="Laporan Aktivitas" class="hover:shadow-lg transition-all cursor-pointer">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Log Aktivitas</h3>
                            <p class="text-sm text-base-content/70">Riwayat aktivitas sistem</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('activity', 'pdf')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            PDF
                        </x-ui.button>
                        <x-ui.button variant="outline" size="xs" onclick="downloadReport('activity', 'excel')">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <!-- Recent Reports -->
        <x-ui.card title="Riwayat Laporan">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Laporan</th>
                            <th>Jenis</th>
                            <th>Periode</th>
                            <th>Dibuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse([] as $key => $report)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="font-semibold">{{ $report->name ?? 'Laporan Penilaian Guru - Periode 1' }}
                                </div>
                            </td>
                            <td>
                                <x-ui.badge variant="primary">
                                    {{ $report->type ?? 'Penilaian' }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="text-sm">{{ $report->period ?? 'Januari - Juni 2024' }}</div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>{{ $report->created_at?->format('d M Y H:i') ?? now()->format('d M Y H:i') }}
                                    </div>
                                    <div class="text-base-content/70">oleh {{ $report->creator ?? 'Admin' }}</div>
                                </div>
                            </td>
                            <td>
                                @php
                                $status = $report->status ?? 'ready';
                                $statusConfig = [
                                'ready' => ['badge' => 'success', 'text' => 'Siap'],
                                'processing' => ['badge' => 'warning', 'text' => 'Processing'],
                                'failed' => ['badge' => 'error', 'text' => 'Gagal'],
                                ];
                                @endphp
                                <x-ui.badge variant="{{ $statusConfig[$status]['badge'] }}">
                                    {{ $statusConfig[$status]['text'] }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    @if($status === 'ready')
                                    <x-ui.button variant="outline" size="xs"
                                        onclick="downloadExistingReport('{{ $report->id ?? '1' }}', 'pdf')">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3" />
                                        </svg>
                                    </x-ui.button>
                                    <x-ui.button variant="outline" size="xs" onclick="viewReportModal.showModal()">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </x-ui.button>
                                    @elseif($status === 'processing')
                                    <div class="loading loading-spinner loading-xs"></div>
                                    @else
                                    <x-ui.button variant="error" size="xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="text-center">
                                        <p class="font-semibold">Belum ada laporan</p>
                                        <p class="text-sm text-base-content/70">Generate laporan pertama Anda</p>
                                    </div>
                                    <x-ui.button variant="primary" size="sm" onclick="generateReportModal.showModal()">
                                        Generate Laporan
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <!-- Generate Report Modal -->
    <x-ui.modal id="generateReportModal" title="Generate Laporan">
        <form method="POST" action="#" class="space-y-4">
            @csrf
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Jenis Laporan <span class="text-error">*</span></span>
                </label>
                <x-ui.select name="report_type" required>
                    <option value="">Pilih jenis laporan</option>
                    <option value="assessment">Laporan Penilaian</option>
                    <option value="ranking">Laporan Ranking</option>
                    <option value="activity">Laporan Aktivitas</option>
                    <option value="summary">Laporan Ringkasan</option>
                </x-ui.select>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Periode <span class="text-error">*</span></span>
                </label>
                <x-ui.select name="period" required>
                    <option value="">Pilih periode</option>
                    <option value="period1">Periode 1 - 2024 (Jan - Jun)</option>
                    <option value="period2">Periode 2 - 2024 (Jul - Des)</option>
                    <option value="custom">Custom Range</option>
                </x-ui.select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal Mulai</span>
                    </label>
                    <x-ui.input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" />
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Tanggal Selesai</span>
                    </label>
                    <x-ui.input type="date" name="end_date" value="{{ now()->endOfMonth()->format('Y-m-d') }}" />
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Format Output <span class="text-error">*</span></span>
                </label>
                <div class="flex gap-4">
                    <label class="label cursor-pointer gap-2">
                        <x-ui.radio name="format" value="pdf" checked />
                        <span class="label-text">PDF</span>
                    </label>
                    <label class="label cursor-pointer gap-2">
                        <x-ui.radio name="format" value="excel" />
                        <span class="label-text">Excel</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Opsi Tambahan</span>
                </label>
                <div class="space-y-2">
                    <label class="label cursor-pointer justify-start gap-2">
                        <x-ui.checkbox name="include_charts" />
                        <span class="label-text">Sertakan grafik</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-2">
                        <x-ui.checkbox name="include_details" />
                        <span class="label-text">Sertakan detail lengkap</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-2">
                        <x-ui.checkbox name="include_summary" />
                        <span class="label-text">Sertakan ringkasan</span>
                    </label>
                </div>
            </div>

            <x-slot name="actions">
                <x-ui.button variant="outline" type="button" onclick="generateReportModal.close()">
                    Batal
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    Generate
                </x-ui.button>
            </x-slot>
        </form>
    </x-ui.modal>

    <!-- View Report Modal -->
    <x-ui.modal id="viewReportModal" title="Preview Laporan">
        <div class="space-y-4">
            <div class="bg-base-200 p-4 rounded-lg">
                <h3 class="font-semibold mb-2">Laporan Penilaian Guru - Periode 1</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium">Periode:</span> Januari - Juni 2024
                    </div>
                    <div>
                        <span class="font-medium">Total Guru:</span> 45
                    </div>
                    <div>
                        <span class="font-medium">Penilaian Selesai:</span> 42
                    </div>
                    <div>
                        <span class="font-medium">Rata-rata Skor:</span> 8.5
                    </div>
                </div>
            </div>

            <div class="text-center py-8">
                <svg class="w-24 h-24 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-base-content/70">Preview laporan lengkap tersedia setelah download</p>
            </div>
        </div>
        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="viewReportModal.close()">
                Tutup
            </x-ui.button>
            <x-ui.button variant="primary" onclick="downloadExistingReport('1', 'pdf')">
                Download
            </x-ui.button>
        </x-slot>
    </x-ui.modal>

    <script>
    function downloadReport(type, format) {
        // Simulate download action
        const fileName = `laporan-${type}-${new Date().toISOString().slice(0, 10)}.${format}`;
        console.log(`Downloading ${fileName}`);

        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-end';
        toast.innerHTML = `
        <div class="alert alert-success">
            <span>Download ${fileName} dimulai</span>
        </div>
    `;
        document.body.appendChild(toast);

        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }

    function downloadExistingReport(id, format) {
        // Simulate download existing report
        const fileName = `laporan-${id}.${format}`;
        console.log(`Downloading existing report ${fileName}`);

        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-end';
        toast.innerHTML = `
        <div class="alert alert-success">
            <span>Download ${fileName} dimulai</span>
        </div>
    `;
        document.body.appendChild(toast);

        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }
    </script>

</x-layouts.admin>
