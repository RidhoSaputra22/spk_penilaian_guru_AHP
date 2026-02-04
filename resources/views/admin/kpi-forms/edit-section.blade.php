<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li><a href="{{ route('admin.kpi-forms.builder', $template) }}">Form Builder</a></li>
        <li>Edit Seksi</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Seksi</h1>
                <p class="text-base-content/60">
                    {{ $template->name }} • Versi {{ $version->version ?? '1.0' }}
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
            <form method="POST" action="{{ route('admin.kpi-forms.update-section', $section) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Section Title -->
                <x-ui.input name="title" label="Judul Seksi" placeholder="Contoh: Kompetensi Pedagogik" required
                    value="{{ old('title', $section->title) }}" />

                <!-- Criteria Selection -->
                @if(!empty($criteriaOptions))
                <x-ui.select name="criteria_node_id" label="Kriteria (opsional)" :options="$criteriaOptions"
                    selected="{{ old('criteria_node_id', $section->criteria_node_id) }}" />
                @endif

                <!-- Sort Order -->
                <x-ui.input name="sort_order" label="Urutan" type="number" placeholder="1"
                    value="{{ old('sort_order', $section->sort_order) }}" />

                <!-- Section Description -->
                <x-ui.textarea name="description" label="Deskripsi" rows="4"
                    placeholder="Deskripsi singkat tentang seksi ini..."
                    value="{{ old('description', $section->description) }}" />

                <!-- Help Text -->
                <x-ui.alert type="info">
                    <span>Seksi digunakan untuk mengelompokkan item penilaian berdasarkan kategori atau kriteria
                        tertentu.</span>
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
