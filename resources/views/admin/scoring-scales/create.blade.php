<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.scoring-scales.index') }}">Skala Penilaian</a></li>
        <li>Tambah Skala Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah Skala Penilaian</h1>
                <p class="text-base-content/60">Buat skala penilaian baru untuk evaluasi guru</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card>
                @if($errors->any())
                <x-ui.alert type="error" class="mb-6">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('admin.scoring-scales.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Nama Skala" placeholder="Contoh: Skala Likert 5 Poin"
                                value="{{ old('name') }}" required />

                        </div>
                    </div>

                    <!-- Scale Type -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Tipe Skala</h3>
                        <x-ui.select name="scale_type" label="Tipe Skala" :options="[
                            'numeric' => 'Numerik (1, 2, 3, ...)',
                            'text' => 'Teks (Sangat Baik, Baik, ...)'
                        ]" selected="{{ old('scale_type', 'numeric') }}" required />
                    </div>

                    <!-- Numeric Scale Settings -->
                    <div class="border-b border-base-200 pb-6" id="numeric-settings">
                        <h3 class="text-lg font-medium mb-4">Pengaturan Skala Numerik</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-ui.input name="min_value" label="Nilai Minimum" type="number" step="0.01" placeholder="1"
                                value="{{ old('min_value', 1) }}" />
                            <x-ui.input name="max_value" label="Nilai Maksimum" type="number" step="0.01"
                                placeholder="5" value="{{ old('max_value', 5) }}" />
                            <x-ui.input name="step" label="Step" type="number" step="0.01" placeholder="1"
                                value="{{ old('step', 1) }}" />
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Opsi Skala</h3>
                            <button type="button" onclick="addOption()" class="btn btn-ghost btn-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Opsi
                            </button>
                        </div>

                        @php
                        $defaultLabels = ['Sangat Kurang', 'Kurang', 'Cukup', 'Baik', 'Sangat Baik'];
                        @endphp

                        <div id="options-container" class="space-y-4">
                            @for($i = 0; $i < 5; $i++) <div
                                class="option-item p-4 border border-base-200 rounded-lg bg-base-50">
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                    <div class="md:col-span-2">
                                        <x-ui.input name="options[{{ $i }}][value]" label="Nilai"
                                            value="{{ old('options.'.$i.'.value', $i + 1) }}" required />
                                    </div>
                                    <div class="md:col-span-4">
                                        <x-ui.input name="options[{{ $i }}][label]" label="Label"
                                            value="{{ old('options.'.$i.'.label', $defaultLabels[$i] ?? '') }}"
                                            required />
                                    </div>
                                    <div class="md:col-span-5">
                                        <x-ui.input name="options[{{ $i }}][description]" label="Deskripsi (Opsional)"
                                            value="{{ old('options.'.$i.'.description') }}" />
                                    </div>
                                    <div class="md:col-span-1 flex justify-center">
                                        @if($i >= 2)
                                        <button type="button" onclick="removeOption(this)"
                                            class="btn btn-error btn-sm btn-square">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                        </div>
                        @endfor
                    </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
            <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">Batal</x-ui.button>
            <x-ui.button type="primary" :isSubmit="true">Simpan Skala</x-ui.button>
        </div>
        </form>
        </x-ui.card>
    </div>

    <div class="lg:col-span-1">
        <x-ui.card title="Panduan">
            <div class="space-y-4">
                <div class="bg-info/10 p-3 rounded-lg">
                    <p class="text-sm font-medium text-info mb-2">Tipe Skala</p>
                    <ul class="text-sm space-y-1 text-base-content/70">
                        <li>• <strong>Numerik:</strong> Menggunakan angka (1-5, 0-10, dll)</li>
                        <li>• <strong>Teks:</strong> Menggunakan kategori teks</li>
                    </ul>
                </div>

                <div class="bg-warning/10 p-3 rounded-lg">
                    <p class="text-sm font-medium text-warning mb-2">Tips</p>
                    <ul class="text-sm space-y-1 text-base-content/70">
                        <li>• Gunakan minimal 2 opsi</li>
                        <li>• Buat label yang jelas dan mudah dipahami</li>
                        <li>• Untuk skala numerik, pastikan range nilai logis</li>
                    </ul>
                </div>

                <div class="bg-success/10 p-3 rounded-lg">
                    <p class="text-sm font-medium text-success mb-2">Contoh Skala</p>
                    <ul class="text-sm space-y-1 text-base-content/70">
                        <li>• <strong>Likert 5:</strong> 1-5 (Sangat Buruk - Sangat Baik)</li>
                        <li>• <strong>Likert 4:</strong> 1-4 (Kurang - Sangat Baik)</li>
                        <li>• <strong>Persentase:</strong> 0-100</li>
                    </ul>
                </div>
            </div>
        </x-ui.card>
    </div>
    </div>

    <script>
    let optionIndex = 5;

    function addOption() {
        const container = document.getElementById('options-container');
        const scaleType = document.querySelector('[name="scale_type"]').value;
        const defaultValue = scaleType === 'numeric' ? optionIndex + 1 : '';

        const optionHtml = `
            <div class="option-item p-4 border border-base-200 rounded-lg bg-base-50">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="label"><span class="label-text">Nilai <span class="text-error">*</span></span></label>
                        <input type="text" name="options[${optionIndex}][value]" value="${defaultValue}"
                               class="input input-bordered w-full" required />
                    </div>
                    <div class="md:col-span-4">
                        <label class="label"><span class="label-text">Label <span class="text-error">*</span></span></label>
                        <input type="text" name="options[${optionIndex}][label]"
                               class="input input-bordered w-full" required />
                    </div>
                    <div class="md:col-span-5">
                        <label class="label"><span class="label-text">Deskripsi (Opsional)</span></label>
                        <input type="text" name="options[${optionIndex}][description]"
                               class="input input-bordered w-full" />
                    </div>
                    <div class="md:col-span-1 flex justify-center">
                        <button type="button" onclick="removeOption(this)" class="btn btn-error btn-sm btn-square">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', optionHtml);
        optionIndex++;
    }

    function removeOption(button) {
        const container = document.getElementById('options-container');
        const optionsCount = container.querySelectorAll('.option-item').length;

        if (optionsCount > 2) {
            button.closest('.option-item').remove();
        } else {
            alert('Skala harus memiliki minimal 2 opsi.');
        }
    }

    // Toggle numeric settings visibility
    document.addEventListener('DOMContentLoaded', function() {
        const scaleTypeSelect = document.querySelector('[name="scale_type"]');
        const numericSettings = document.getElementById('numeric-settings');

        function toggleNumericSettings() {
            if (scaleTypeSelect.value === 'numeric') {
                numericSettings.style.display = 'block';
            } else {
                numericSettings.style.display = 'none';
            }
        }

        scaleTypeSelect.addEventListener('change', toggleNumericSettings);
        toggleNumericSettings();
    });
    </script>
</x-layouts.admin>
