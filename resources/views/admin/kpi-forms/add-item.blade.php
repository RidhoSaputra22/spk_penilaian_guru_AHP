<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li><a href="{{ route('admin.kpi-forms.builder', $template) }}">Form Builder</a></li>
        <li>Tambah Item</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah Item Baru</h1>
                <p class="text-base-content/60">
                    {{ $template->name }} • Seksi: {{ $section->title }}
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

    <div class="">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.kpi-forms.add-item', $version) }}" class="space-y-6">
                @csrf
                <input type="hidden" name="section_id" value="{{ $section->id }}">

                <!-- Item Label -->
                <x-ui.input name="label" label="Label Item" placeholder="Contoh: Kemampuan merencanakan pembelajaran"
                    required value="{{ old('label') }}" />

                <!-- Field Type -->
                <x-ui.select name="field_type" label="Tipe Field" :options="$fieldTypes"
                    selected="{{ old('field_type', 'numeric') }}" required />

                <!-- Criteria Selection -->
                @if(!empty($criteriaOptions))
                <x-ui.select name="criteria_node_id" label="Kriteria (opsional)" :options="$criteriaOptions"
                    selected="{{ old('criteria_node_id') }}" />
                @endif

                <!-- Min/Max Values -->
                <div class="grid grid-cols-2 gap-4">
                    <x-ui.input name="min_value" label="Nilai Minimum" type="number" step="0.01" placeholder="0"
                        value="{{ old('min_value') }}" />
                    <x-ui.input name="max_value" label="Nilai Maximum" type="number" step="0.01" placeholder="100"
                        value="{{ old('max_value') }}" />
                </div>

                <!-- Help Text -->
                <x-ui.textarea name="help_text" label="Teks Bantuan" rows="3"
                    placeholder="Deskripsi atau petunjuk untuk pengisian item ini..." value="{{ old('help_text') }}" />

                <!-- Is Required -->
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_required" value="1" class="checkbox checkbox-primary"
                            {{ old('is_required') ? 'checked' : '' }}>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Simpan Item
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layouts.admin>
