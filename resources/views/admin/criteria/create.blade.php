<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.criteria.index') }}">Kriteria</a></li>
        <li>Tambah Set Kriteria</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah Set Kriteria Baru</h1>
                <p class="text-base-content/60">Buat set kriteria untuk penilaian kinerja guru</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
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

                <form method="POST" action="{{ route('admin.criteria.store-set') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Info -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Set Kriteria</h3>
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Nama Set Kriteria"
                                placeholder="Contoh: Kriteria Penilaian Guru 2024" value="{{ old('name') }}"
                                error="{{ $errors->first('name') }}" required />
                            <x-ui.textarea name="description" label="Deskripsi"
                                placeholder="Deskripsi singkat tentang set kriteria ini..."
                                value="{{ old('description') }}" error="{{ $errors->first('description') }}" rows="3" />
                        </div>
                    </div>

                    <!-- Criteria Nodes -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Kriteria dan Indikator</h3>
                        <div id="criteria-container" class="space-y-4">
                            <!-- Template for dynamic criteria -->
                            <div class="criteria-item bg-base-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <x-ui.input name="criteria[0][name]" label="Nama Kriteria"
                                        placeholder="Contoh: Kompetensi Pedagogik" value="{{ old('criteria.0.name') }}"
                                        required />
                                    <x-ui.input name="criteria[0][weight]" label="Bobot (%)" type="number"
                                        placeholder="25" min="1" max="100" value="{{ old('criteria.0.weight') }}"
                                        required />
                                </div>
                                <x-ui.textarea name="criteria[0][description]" label="Deskripsi Kriteria"
                                    placeholder="Kemampuan mengelola pembelajaran peserta didik..."
                                    value="{{ old('criteria.0.description') }}" rows="2" />

                                <!-- Sub-criteria/Indicators -->
                                <div class="mt-4">
                                    <h4 class="font-medium mb-2">Sub-kriteria/Indikator</h4>
                                    <div class="sub-criteria-container space-y-2">
                                        <div class="flex gap-2">
                                            <x-ui.input name="criteria[0][sub_criteria][0][name]"
                                                placeholder="Nama sub-kriteria atau indikator"
                                                value="{{ old('criteria.0.sub_criteria.0.name') }}" class="flex-1" />
                                            <x-ui.input name="criteria[0][sub_criteria][0][weight]" type="number"
                                                placeholder="Bobot" min="1" max="100"
                                                value="{{ old('criteria.0.sub_criteria.0.weight') }}" class="w-24" />
                                            <button type="button" class="btn btn-error btn-sm"
                                                onclick="removeSubCriteria(this)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline btn-sm mt-2"
                                        onclick="addSubCriteria(this)">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Sub-kriteria
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-criteria" class="btn btn-outline mt-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Kriteria
                        </button>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Set Kriteria
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-ui.card title="Panduan">
                <div class="space-y-4">
                    <div class="alert alert-info">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Tips Membuat Kriteria</p>
                            <ul class="text-sm mt-2 space-y-1">
                                <li>• Gunakan kriteria yang jelas dan terukur</li>
                                <li>• Total bobot semua kriteria = 100%</li>
                                <li>• Setiap kriteria sebaiknya memiliki sub-kriteria</li>
                            </ul>
                        </div>
                    </div>

                    @if($scoringScales->count() > 0)
                    <div>
                        <h4 class="font-semibold mb-2">Skala Penilaian Tersedia</h4>
                        <div class="space-y-2">
                            @foreach($scoringScales as $scale)
                            <div class="text-sm p-2 bg-base-50 rounded">
                                <p class="font-medium">{{ $scale->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $scale->options->count() }} opsi nilai</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
    let criteriaIndex = 1;

    document.getElementById('add-criteria').addEventListener('click', function() {
        const container = document.getElementById('criteria-container');
        const template = `
                <div class="criteria-item bg-base-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Nama Kriteria <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="criteria[${criteriaIndex}][name]" placeholder="Contoh: Kompetensi Profesional" class="input input-bordered" required>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Bobot (%) <span class="text-error">*</span></span>
                            </label>
                            <input type="number" name="criteria[${criteriaIndex}][weight]" placeholder="25" min="1" max="100" class="input input-bordered" required>
                        </div>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Deskripsi Kriteria</span>
                        </label>
                        <textarea name="criteria[${criteriaIndex}][description]" placeholder="Kemampuan menguasai materi pembelajaran..." rows="2" class="textarea textarea-bordered"></textarea>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Sub-kriteria/Indikator</h4>
                        <div class="sub-criteria-container space-y-2">
                            <div class="flex gap-2">
                                <input type="text" name="criteria[${criteriaIndex}][sub_criteria][0][name]" placeholder="Nama sub-kriteria atau indikator" class="input input-bordered flex-1">
                                <input type="number" name="criteria[${criteriaIndex}][sub_criteria][0][weight]" placeholder="Bobot" min="1" max="100" class="input input-bordered w-24">
                                <button type="button" class="btn btn-error btn-sm" onclick="removeSubCriteria(this)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm mt-2" onclick="addSubCriteria(this)">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Sub-kriteria
                        </button>
                    </div>

                    <button type="button" class="btn btn-error btn-sm mt-4" onclick="removeCriteria(this)">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Kriteria
                    </button>
                </div>
            `;

        container.insertAdjacentHTML('beforeend', template);
        criteriaIndex++;
    });

    function removeCriteria(button) {
        if (document.querySelectorAll('.criteria-item').length > 1) {
            button.closest('.criteria-item').remove();
        } else {
            alert('Minimal harus ada satu kriteria');
        }
    }

    function addSubCriteria(button) {
        const container = button.previousElementSibling;
        const criteriaIndex = Array.from(container.closest('#criteria-container').children).indexOf(container.closest(
            '.criteria-item'));
        const subIndex = container.children.length;

        const template = `
                <div class="flex gap-2">
                    <input type="text" name="criteria[${criteriaIndex}][sub_criteria][${subIndex}][name]" placeholder="Nama sub-kriteria atau indikator" class="input input-bordered flex-1">
                    <input type="number" name="criteria[${criteriaIndex}][sub_criteria][${subIndex}][weight]" placeholder="Bobot" min="1" max="100" class="input input-bordered w-24">
                    <button type="button" class="btn btn-error btn-sm" onclick="removeSubCriteria(this)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            `;

        container.insertAdjacentHTML('beforeend', template);
    }

    function removeSubCriteria(button) {
        const container = button.closest('.sub-criteria-container');
        if (container.children.length > 1) {
            button.closest('.flex').remove();
        } else {
            alert('Minimal harus ada satu sub-kriteria');
        }
    }
    </script>
</x-layouts.admin>
