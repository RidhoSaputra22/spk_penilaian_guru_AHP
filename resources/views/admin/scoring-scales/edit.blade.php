<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.scoring-scales.index') }}">Skala Penilaian</a></li>
        <li>Edit - {{ $scoringScale->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Skala Penilaian</h1>
                <p class="text-base-content/60">Perbarui skala penilaian {{ $scoringScale->name }}</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.show', $scoringScale) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat Detail
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
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

                <form method="POST" action="{{ route('admin.scoring-scales.update', $scoringScale) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Nama Skala" placeholder="Contoh: Skala Likert 5 Poin"
                                value="{{ old('name', $scoringScale->name) }}" required />

                        </div>
                    </div>

                    <!-- Scale Type -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Tipe Skala</h3>
                        <x-ui.select name="scale_type" label="Tipe Skala" :options="[
                            'numeric' => 'Numerik (1, 2, 3, ...)',
                            'text' => 'Teks (Sangat Baik, Baik, ...)'
                        ]" selected="{{ old('scale_type', $scoringScale->scale_type) }}" required />
                    </div>

                    <!-- Numeric Scale Settings -->
                    <div class="border-b border-base-200 pb-6" id="numeric-settings"
                        style="{{ $scoringScale->scale_type === 'numeric' ? '' : 'display: none;' }}">
                        <h3 class="text-lg font-medium mb-4">Pengaturan Skala Numerik</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-ui.input name="min_value" label="Nilai Minimum" type="number" step="0.01" placeholder="1"
                                value="{{ old('min_value', $scoringScale->min_value) }}" />
                            <x-ui.input name="max_value" label="Nilai Maksimum" type="number" step="0.01"
                                placeholder="5" value="{{ old('max_value', $scoringScale->max_value) }}" />
                            <x-ui.input name="step" label="Step" type="number" step="0.01" placeholder="1"
                                value="{{ old('step', $scoringScale->step) }}" />
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Opsi Skala</h3>
                            <x-ui.button type="ghost" size="sm" onclick="addOption()" id="add-option-btn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Opsi
                            </x-ui.button>
                        </div>

                        <div id="options-container">
                            @foreach($scoringScale->options->sortBy('sort_order') as $index => $option)
                            <div class="option-item grid grid-cols-12 gap-4 mb-4 p-4 border border-base-200 rounded-lg">
                                <div class="col-span-2">
                                    <x-ui.input name="options[{{ $index }}][value]" label="Nilai"
                                        value="{{ old('options.'.$index.'.value', $option->value) }}" required />
                                </div>
                                <div class="col-span-4">
                                    <x-ui.input name="options[{{ $index }}][label]" label="Label"
                                        value="{{ old('options.'.$index.'.label', $option->label) }}" required />
                                </div>
                                <div class="col-span-5">
                                    <x-ui.input name="options[{{ $index }}][description]" label="Deskripsi (Opsional)"
                                        value="{{ old('options.'.$index.'.description', $option->description) }}" />
                                </div>
                                <div class="col-span-1 flex items-end">
                                    @if($scoringScale->options->count() > 2)
                                    <x-ui.button type="error" size="sm" onclick="removeOption(this)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </x-ui.button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">Batal</x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true">Update Skala</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card title="Informasi Skala">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">Skala Saat Ini</p>
                        <p class="text-sm">{{ $scoringScale->name }}</p>
                        <p class="text-xs text-base-content/60 mt-1">{{ $scoringScale->options->count() }} opsi</p>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Dibuat</p>
                        <p class="text-xs">{{ $scoringScale->created_at->format('d M Y H:i') }}</p>
                    </div>

                    @if($scoringScale->updated_at != $scoringScale->created_at)
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Terakhir Diupdate</p>
                        <p class="text-xs">{{ $scoringScale->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif

                    <div class="alert alert-warning">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Perhatian</p>
                            <p class="text-sm">Perubahan pada skala ini akan mempengaruhi semua penilaian yang
                                menggunakan skala ini.</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
    let optionIndex = {
        {
            $scoringScale - > options - > count()
        }
    };

    function addOption() {
        const container = document.getElementById('options-container');
        const scaleType = document.querySelector('[name="scale_type"]').value;
        const defaultValue = scaleType === 'numeric' ? optionIndex + 1 : '';

        const optionHtml = `
            <div class="option-item grid grid-cols-12 gap-4 mb-4 p-4 border border-base-200 rounded-lg">
                <div class="col-span-2">
                    <label class="label"><span class="label-text">Nilai</span></label>
                    <input type="text" name="options[${optionIndex}][value]" value="${defaultValue}"
                           class="input input-bordered w-full" required />
                </div>
                <div class="col-span-4">
                    <label class="label"><span class="label-text">Label</span></label>
                    <input type="text" name="options[${optionIndex}][label]"
                           class="input input-bordered w-full" required />
                </div>
                <div class="col-span-5">
                    <label class="label"><span class="label-text">Deskripsi (Opsional)</span></label>
                    <input type="text" name="options[${optionIndex}][description]"
                           class="input input-bordered w-full" />
                </div>
                <div class="col-span-1 flex items-end">
                    <button type="button" onclick="removeOption(this)"
                            class="btn btn-error btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', optionHtml);
        optionIndex++;
    }

    function removeOption(button) {
        const optionsCount = document.querySelectorAll('.option-item').length;
        if (optionsCount > 2) {
            button.closest('.option-item').remove();
        } else {
            alert('Skala harus memiliki minimal 2 opsi.');
        }
    }

    // Toggle numeric settings visibility
    document.querySelector('[name="scale_type"]').addEventListener('change', function() {
        const numericSettings = document.getElementById('numeric-settings');
        if (this.value === 'numeric') {
            numericSettings.style.display = 'block';
        } else {
            numericSettings.style.display = 'none';
        }
    });
    </script>
</x-layouts.admin>