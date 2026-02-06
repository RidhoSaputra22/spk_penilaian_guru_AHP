<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li><a href="{{ route('admin.kpi-forms.builder', $template) }}">Form Builder</a></li>
        <li>Edit Item</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Item</h1>
                <p class="text-base-content/60">
                    {{ $template->name }} • Seksi: {{ $item->section->title }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.builder', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <div class="" x-data="{
        fieldType: '{{ old('field_type', $item->field_type) }}',
        options: @js(old('options', $item->options->map(fn($o) => ['id' => $o->id, 'value' => $o->value, 'label' => $o->label, 'score_value' => $o->score_value])->toArray()) ?: [['id' => '', 'value' => '', 'label' => '', 'score_value' => '']]),
        addOption() {
            this.options.push({ id: '', value: '', label: '', score_value: '' });
        },
        removeOption(index) {
            if (this.options.length > 1) {
                this.options.splice(index, 1);
            }
        },
        get showOptions() {
            return this.fieldType === 'dropdown' || this.fieldType === 'radio';
        },
        validateUniqueValues() {
            const values = this.options.map(opt => opt.value).filter(val => val);
            const uniqueValues = new Set(values);
            return values.length === uniqueValues.size;
        },
        getDuplicateValues() {
            const values = this.options.map(opt => opt.value).filter(val => val);
            const seen = new Set();
            const duplicates = new Set();
            values.forEach(val => {
                if (seen.has(val)) {
                    duplicates.add(val);
                } else {
                    seen.add(val);
                }
            });
            return Array.from(duplicates);
        }
    }">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.kpi-forms.update-item', $item) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Item Label -->
                <x-ui.input name="label" label="Label Item" placeholder="Contoh: Kemampuan merencanakan pembelajaran"
                    required value="{{ old('label', $item->label) }}" />

                <!-- Field Type -->
                <div class="form-control w-full">
                    <label class="label" for="field_type">
                        <span class="label-text">Tipe Field <span class="text-error">*</span></span>
                    </label>
                    <select name="field_type" id="field_type" x-model="fieldType" class="select select-bordered w-full" required>
                        <option value="">Pilih...</option>
                        @foreach($fieldTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('field_type', $item->field_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('field_type')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <!-- Criteria Selection -->
                @if(!empty($criteriaOptions))
                <x-ui.select name="criteria_node_id" label="Kriteria (opsional)" :options="$criteriaOptions"
                    selected="{{ old('criteria_node_id', $item->criteria_node_id) }}" />
                @endif

                <!-- Scoring Scale Selection (only for numeric) -->
                @if(!empty($scoringScales))
                <div x-show="fieldType === 'numeric'" x-cloak>
                    <x-ui.select name="scoring_scale_id" label="Skala Penilaian (opsional)" :options="$scoringScales"
                        selected="{{ old('scoring_scale_id', $item->scoring_scale_id) }}" />
                </div>
                @endif

                <!-- Options for dropdown/radio -->
                <div x-show="showOptions" x-cloak class="space-y-4">
                    <div class="divider">Opsi Pilihan</div>

                    <x-ui.alert type="info">
                        <span>Tambahkan opsi yang akan ditampilkan kepada penilai. Score value digunakan untuk perhitungan. Nilai opsi harus unik.</span>
                    </x-ui.alert>

                    <div x-show="!validateUniqueValues()" x-cloak class="alert alert-warning mb-4">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span x-text="'Nilai duplikat ditemukan: ' + getDuplicateValues().join(', ')"></span>
                    </div>

                    <template x-for="(option, index) in options" :key="index">
                        <div class="flex gap-3 items-end p-4 bg-base-200 rounded-lg">
                            <input type="hidden" x-bind:name="'options[' + index + '][id]'" x-model="option.id">
                            <div class="flex-1">
                                <label class="label"><span class="label-text">Label</span></label>
                                <input type="text"
                                    x-bind:name="'options[' + index + '][label]'"
                                    x-model="option.label"
                                    class="input input-bordered w-full"
                                    placeholder="Contoh: Sangat Baik">
                            </div>
                            <div class="w-32">
                                <label class="label"><span class="label-text">Value</span></label>
                                <input type="text"
                                    x-bind:name="'options[' + index + '][value]'"
                                    x-model="option.value"
                                    x-bind:class="getDuplicateValues().includes(option.value) && option.value ? 'input input-bordered w-full border-error' : 'input input-bordered w-full'"
                                    placeholder="Contoh: 5">
                                <div x-show="getDuplicateValues().includes(option.value) && option.value" x-cloak class="text-error text-xs mt-1">
                                    Nilai sudah digunakan
                                </div>
                            </div>
                            <div class="w-32">
                                <label class="label"><span class="label-text">Score</span></label>
                                <input type="number"
                                    step="0.01"
                                    x-bind:name="'options[' + index + '][score_value]'"
                                    x-model="option.score_value"
                                    class="input input-bordered w-full"
                                    placeholder="Contoh: 100">
                            </div>
                            <button type="button"
                                x-on:click="removeOption(index)"
                                class="btn btn-circle btn-ghost btn-sm text-error"
                                x-bind:disabled="options.length <= 1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <button type="button" x-on:click="addOption()" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Opsi
                    </button>
                </div>

                <!-- Min/Max Values (only for numeric) -->
                <div x-show="fieldType === 'numeric'" x-cloak class="grid grid-cols-2 gap-4">
                    <x-ui.input name="min_value" label="Nilai Minimum" type="number" step="0.01" placeholder="0"
                        value="{{ old('min_value', $item->min_value) }}" />
                    <x-ui.input name="max_value" label="Nilai Maximum" type="number" step="0.01" placeholder="100"
                        value="{{ old('max_value', $item->max_value) }}" />
                </div>

                <!-- Default Value -->
                <x-ui.input name="default_value" label="Nilai Default (opsional)" placeholder="Nilai awal jika ada..."
                    value="{{ old('default_value', $item->default_value) }}" />

                <!-- Sort Order -->
                <x-ui.input name="sort_order" label="Urutan" type="number" min="0"
                    value="{{ old('sort_order', $item->sort_order) }}" />

                <!-- Help Text -->
                <x-ui.textarea name="help_text" label="Teks Bantuan" rows="3"
                    placeholder="Deskripsi atau petunjuk untuk pengisian item ini..."
                    value="{{ old('help_text', $item->help_text) }}" />

                <!-- Is Required -->
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_required" value="1" class="checkbox checkbox-primary"
                            {{ old('is_required', $item->is_required) ? 'checked' : '' }}>
                        <span class="label-text">Item ini wajib diisi</span>
                    </label>
                </div>

                <!-- Help Text -->
                <x-ui.alert type="info">
                    <span>Item adalah field input yang akan diisi oleh penilai saat melakukan penilaian.</span>
                </x-ui.alert>

                @if($errors->any())
                <x-ui.alert type="error">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
                @endif

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4">
                    <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.builder', $template) }}">
                        Batal
                    </x-ui.button>
                    <x-ui.button type="primary" :isSubmit="true">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layouts.admin>
