<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.criteria.index') }}">Kriteria</a></li>
        <li>Edit Set Kriteria - {{ $criteriaSet->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Set Kriteria</h1>
                <p class="text-base-content/60">Perbarui informasi set kriteria {{ $criteriaSet->name }}</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card>
                <form method="POST" action="{{ route('admin.criteria.update-set', $criteriaSet) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Set Kriteria</h3>
                        <div class="space-y-4">
                            <x-ui.input
                                name="name"
                                label="Nama Set Kriteria"
                                placeholder="Contoh: Kriteria Penilaian Guru 2024"
                                value="{{ old('name', $criteriaSet->name) }}"
                                error="{{ $errors->first('name') }}"
                                required
                            />
                            <x-ui.textarea
                                name="description"
                                label="Deskripsi"
                                placeholder="Deskripsi singkat tentang set kriteria ini..."
                                value="{{ old('description', $criteriaSet->description) }}"
                                error="{{ $errors->first('description') }}"
                                rows="3"
                            />
                        </div>
                    </div>

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Pengaturan Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                name="version"
                                label="Versi"
                                placeholder="1.0"
                                value="{{ old('version', $criteriaSet->version) }}"
                                error="{{ $errors->first('version') }}"
                            />
                            <div class="form-control">
                                <label class="label"><span class="label-text font-semibold">Status Aktif</span></label>
                                <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" @checked(old('is_active', $criteriaSet->is_active)) @disabled($criteriaSet->locked_at) />
                                <div class="label"><span class="label-text-alt text-base-content/60">Set aktif akan digunakan untuk periode penilaian baru</span></div>
                            </div>
                        </div>
                    </div>

                    @if($criteriaSet->locked_at)
                        <div class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Informasi Kunci</h3>
                            <x-ui.alert type="warning">
                                <div>
                                    <p class="font-semibold">Set Kriteria Terkunci</p>
                                    <p class="text-sm">Dikunci pada: {{ $criteriaSet->locked_at->format('d M Y H:i') }}</p>
                                </div>
                            </x-ui.alert>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">Batal</x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true" :disabled="$criteriaSet->locked_at">Update Set Kriteria</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card title="Informasi Set">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">Set Saat Ini</p>
                        <p class="text-sm">{{ $criteriaSet->name }}</p>
                        <p class="text-sm text-base-content/60">Versi {{ $criteriaSet->version }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            @if($criteriaSet->is_active)
                                <span class="badge badge-success badge-sm">Aktif</span>
                            @else
                                <span class="badge badge-ghost badge-sm">Tidak Aktif</span>
                            @endif
                            @if($criteriaSet->locked_at)
                                <span class="badge badge-warning badge-sm">Terkunci</span>
                            @endif
                        </div>
                    </div>
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Statistik</p>
                        <p class="text-sm">Total Kriteria: {{ $criteriaSet->nodes()->where('node_type', 'criteria')->count() }}</p>
                        <p class="text-sm">Total Sub-kriteria: {{ $criteriaSet->nodes()->where('node_type', 'subcriteria')->count() }}</p>
                    </div>
                    @if($criteriaSet->locked_at)
                        <div class="alert alert-warning">
                            <p class="font-semibold">Set Terkunci</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p class="text-sm">Set akan terkunci otomatis setelah digunakan dalam periode penilaian aktif.</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
