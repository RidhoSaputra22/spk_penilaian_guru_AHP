<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Pembobotan AHP</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Pembobotan AHP</h1>
                <p class="text-base-content/60">Atur bobot kriteria menggunakan Analytic Hierarchy Process</p>
            </div>
            @if(isset($ahpModel) && $ahpModel->status !== 'finalized')
                <x-ui.button type="success" onclick="document.getElementById('finalize-modal').showModal()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Finalisasi Bobot
                </x-ui.button>
            @endif
        </div>
    </x-slot:header>

    <!-- Period Selection -->
    <x-ui.card class="mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1">
                <x-ui.select
                    name="period_id"
                    label="Pilih Periode Penilaian"
                    :options="$periods ?? []"
                    :value="$selectedPeriod->id ?? ''"
                    id="period-select"
                />
            </div>
            <x-ui.button type="primary" onclick="changePeriod()">
                Pilih Periode
            </x-ui.button>
        </div>
    </x-ui.card>

    @if(isset($selectedPeriod) && isset($ahpModel))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- AHP Status -->
                <x-ui.card>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold">{{ $selectedPeriod->name }}</h3>
                            <p class="text-sm text-base-content/60">Set Kriteria: {{ $ahpModel->criteriaSet->name ?? 'N/A' }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Consistency Ratio -->
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ ($ahpModel->consistency_ratio ?? 1) <= 0.1 ? 'text-success' : 'text-error' }}">
                                    {{ number_format(($ahpModel->consistency_ratio ?? 0) * 100, 2) }}%
                                </div>
                                <div class="text-xs text-base-content/60">Consistency Ratio</div>
                            </div>
                            <!-- Status -->
                            <div>
                                @if($ahpModel->status === 'finalized')
                                    <x-ui.badge type="success" size="lg">Finalized</x-ui.badge>
                                @else
                                    <x-ui.badge type="warning" size="lg">Draft</x-ui.badge>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(($ahpModel->consistency_ratio ?? 1) > 0.1 && $ahpModel->status !== 'finalized')
                        <x-ui.alert type="warning" class="mt-4">
                            <strong>Perhatian:</strong> Consistency Ratio melebihi 10%. Mohon perbaiki perbandingan berpasangan agar hasil lebih konsisten.
                        </x-ui.alert>
                    @endif
                </x-ui.card>

                <!-- Pairwise Comparison -->
                <x-ui.card title="Perbandingan Berpasangan Kriteria">
                    @if($ahpModel->status === 'finalized')
                        <x-ui.alert type="info" class="mb-4">
                            Bobot sudah di-finalisasi. Tidak dapat mengubah perbandingan.
                        </x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('admin.ahp.store-comparisons', $ahpModel) }}" id="comparison-form">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="bg-base-200">Kriteria A</th>
                                        <th class="bg-base-200 text-center">Skala</th>
                                        <th class="bg-base-200">Kriteria B</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comparisons ?? [] as $comparison)
                                        <tr>
                                            <td class="font-medium">
                                                {{ $comparison->nodeA?->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="flex items-center justify-center gap-2">
                                                    @php
                                                        $scales = [9, 7, 5, 3, 1, '1/3', '1/5', '1/7', '1/9'];
                                                        $currentValue = $comparison->value ?? 1;
                                                    @endphp

                                                    <span class="text-xs text-base-content/60">← Lebih penting</span>

                                                    <select
                                                        name="comparisons[{{ $comparison->nodeA?->id }}][{{ $comparison->nodeB?->id }}]"
                                                        class="select select-bordered select-sm w-24"
                                                        {{ $ahpModel->status === 'finalized' ? 'disabled' : '' }}
                                                    >
                                                        @foreach($scales as $scale)
                                                            @php
                                                                $numericScale = is_numeric($scale) ? $scale : eval("return $scale;");
                                                            @endphp
                                                            <option value="{{ $numericScale }}" {{ abs($currentValue - $numericScale) < 0.01 ? 'selected' : '' }}>
                                                                {{ $scale }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    <span class="text-xs text-base-content/60">Lebih penting →</span>
                                                </div>
                                            </td>
                                            <td class="font-medium text-right">
                                                {{ $comparison->nodeB?->name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-8 text-base-content/60">
                                                Tidak ada kriteria untuk dibandingkan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($ahpModel->status !== 'finalized' && count($comparisons ?? []) > 0)
                            <div class="flex justify-end mt-4">
                                <x-ui.button type="primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Hitung Ulang Bobot
                                </x-ui.button>
                            </div>
                        @endif
                    </form>
                </x-ui.card>

                <!-- Scale Reference -->
                <x-ui.card title="Referensi Skala Saaty" compact>
                    <div class="overflow-x-auto">
                        <table class="table table-xs">
                            <thead>
                                <tr>
                                    <th>Skala</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="font-mono">1</td><td>Sama pentingnya</td></tr>
                                <tr><td class="font-mono">3</td><td>Sedikit lebih penting</td></tr>
                                <tr><td class="font-mono">5</td><td>Lebih penting</td></tr>
                                <tr><td class="font-mono">7</td><td>Sangat lebih penting</td></tr>
                                <tr><td class="font-mono">9</td><td>Mutlak lebih penting</td></tr>
                                <tr><td class="font-mono">1/3, 1/5...</td><td>Kebalikan dari skala di atas</td></tr>
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            <!-- Sidebar - Weights -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Calculated Weights -->
                <x-ui.card title="Bobot Hasil Perhitungan">
                    <div class="space-y-3">
                        @forelse($weights ?? [] as $weight)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                <div>
                                    <div class="font-medium">{{ $weight->criteriaNode->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-base-content/60">{{ $weight->criteriaNode->code ?? '' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-primary">{{ number_format($weight->weight * 100, 2) }}%</div>
                                    <div class="text-xs text-base-content/60">{{ number_format($weight->weight, 4) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-base-content/60">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p>Belum ada bobot</p>
                                <p class="text-sm">Lakukan perbandingan berpasangan terlebih dahulu</p>
                            </div>
                        @endforelse
                    </div>

                    @if(count($weights ?? []) > 0)
                        <div class="divider"></div>
                        <div class="flex justify-between items-center text-sm">
                            <span>Total Bobot</span>
                            <span class="font-bold">{{ number_format(collect($weights)->sum('weight') * 100, 2) }}%</span>
                        </div>
                    @endif
                </x-ui.card>

                <!-- Weight Distribution Chart Placeholder -->
                <x-ui.card title="Distribusi Bobot">
                    <div class="space-y-2">
                        @foreach($weights ?? [] as $weight)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ Str::limit($weight->criteriaNode->name ?? '', 20) }}</span>
                                    <span>{{ number_format($weight->weight * 100, 1) }}%</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ $weight->weight * 100 }}" max="100"></progress>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <!-- Quick Info -->
                <x-ui.card title="Info AHP">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Dibuat</span>
                            <span>{{ $ahpModel->created_at?->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Oleh</span>
                            <span>{{ $ahpModel->creator?->name ?? 'System' }}</span>
                        </div>
                        @if($ahpModel->finalized_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Finalisasi</span>
                                <span>{{ $ahpModel->finalized_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>

        <!-- Finalize Modal -->
        @if($ahpModel->status !== 'finalized')
            <x-ui.modal id="finalize-modal" title="Finalisasi Bobot AHP">
                <p>Anda yakin ingin memfinalisasi bobot AHP untuk periode <strong>{{ $selectedPeriod->name }}</strong>?</p>

                @if(($ahpModel->consistency_ratio ?? 1) > 0.1)
                    <x-ui.alert type="error" class="mt-4">
                        <strong>Peringatan:</strong> Consistency Ratio masih di atas 10% ({{ number_format($ahpModel->consistency_ratio * 100, 2) }}%).
                        Disarankan untuk memperbaiki perbandingan terlebih dahulu.
                    </x-ui.alert>
                @endif

                <p class="text-sm text-base-content/60 mt-4">Setelah difinalisasi:</p>
                <ul class="text-sm text-base-content/60 list-disc list-inside">
                    <li>Bobot tidak dapat diubah lagi</li>
                    <li>Penilaian dapat dimulai</li>
                    <li>Hasil akan menggunakan bobot ini</li>
                </ul>

                <x-slot:actions>
                    <form method="dialog">
                        <button class="btn btn-ghost">Batal</button>
                    </form>
                    <form method="POST" action="{{ route('admin.ahp.finalize', $ahpModel) }}">
                        @csrf
                        @method('PATCH')
                        <x-ui.button type="success">Finalisasi</x-ui.button>
                    </form>
                </x-slot:actions>
            </x-ui.modal>
        @endif
    @else
        <x-ui.card>
            <div class="text-center py-12 text-base-content/60">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-lg">Pilih Periode</p>
                <p class="text-sm">Pilih periode penilaian untuk mengatur pembobotan AHP</p>
            </div>
        </x-ui.card>
    @endif

    <script>
        function changePeriod() {
            const periodId = document.getElementById('period-select').value;
            if (periodId) {
                window.location.href = `{{ route('admin.ahp.index') }}?period=${periodId}`;
            }
        }
    </script>
</x-layouts.admin>
