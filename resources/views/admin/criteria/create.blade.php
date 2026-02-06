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

                    <!-- Criteria Nodes (Alpine.js) -->
                    <div class="border-b border-base-200 pb-6" x-data="{
                            setName: '',
                            criteria: [{ name: '', description: '', sub_criteria: [{ name: '' }] }],
                            get prefix() {
                                let clean = this.setName.replace(/[^a-zA-Z]/g, '');
                                return clean.substring(0, 3).toUpperCase().padEnd(3, 'X');
                            },
                            criteriaCode(ci) {
                                return this.prefix + '-' + (ci + 1);
                            },
                            subCode(ci, si) {
                                return this.criteriaCode(ci) + '.' + (si + 1);
                            },
                            addCriteria() {
                                this.criteria.push({ name: '', description: '', sub_criteria: [{ name: '' }] });
                            },
                            removeCriteria(index) {
                                if (this.criteria.length > 1) {
                                    this.criteria.splice(index, 1);
                                }
                            },
                            addSubCriteria(ci) {
                                this.criteria[ci].sub_criteria.push({ name: '' });
                            },
                            removeSubCriteria(ci, si) {
                                if (this.criteria[ci].sub_criteria.length > 1) {
                                    this.criteria[ci].sub_criteria.splice(si, 1);
                                }
                            }
                        }" x-init="$watch('$refs.setNameInput?.value', v => setName = v || '')"
                        @input.window="if ($event.target.name === 'name') setName = $event.target.value">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium">Kriteria dan Sub-kriteria</h3>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-info badge-sm">Bobot ditentukan oleh AHP</span>
                                <span class="badge badge-ghost badge-sm" x-show="setName.length > 0"
                                    x-text="'Prefix: ' + prefix"></span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(item, ci) in criteria" :key="ci">
                                <div class="bg-base-200/50 p-4 rounded-lg border border-base-300">
                                    <!-- Criteria Header -->
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="badge badge-primary font-mono"
                                                x-text="criteriaCode(ci)"></span>
                                        </div>
                                        <button type="button" x-show="criteria.length > 1" @click="removeCriteria(ci)"
                                            class="btn btn-ghost btn-xs text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>

                                    <!-- Criteria Fields -->
                                    <div class="mb-3">
                                        <label class="label"><span class="label-text font-semibold">Nama Kriteria <span
                                                    class="text-error">*</span></span></label>
                                        <input type="text" :name="'criteria[' + ci + '][name]'" x-model="item.name"
                                            placeholder="Contoh: Kompetensi Pedagogik"
                                            class="input input-bordered w-full" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="label"><span
                                                class="label-text font-semibold">Deskripsi</span></label>
                                        <textarea :name="'criteria[' + ci + '][description]'" x-model="item.description"
                                            placeholder="Deskripsi kriteria (opsional)" rows="2"
                                            class="textarea textarea-bordered w-full"></textarea>
                                    </div>

                                    <!-- Sub-criteria -->
                                    <div class="bg-base-100 p-3 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-sm font-semibold text-base-content/70">Sub-kriteria /
                                                Indikator</h4>
                                            <button type="button" @click="addSubCriteria(ci)"
                                                class="btn btn-ghost btn-xs">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Tambah
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <template x-for="(sub, si) in item.sub_criteria" :key="si">
                                                <div class="flex items-center gap-2">
                                                    <span class="badge badge-ghost badge-xs font-mono w-20 shrink-0"
                                                        x-text="subCode(ci, si)"></span>
                                                    <input type="text"
                                                        :name="'criteria[' + ci + '][sub_criteria][' + si + '][name]'"
                                                        x-model="sub.name"
                                                        placeholder="Nama sub-kriteria atau indikator"
                                                        class="input input-bordered input-sm flex-1">
                                                    <button type="button" x-show="item.sub_criteria.length > 1"
                                                        @click="removeSubCriteria(ci, si)"
                                                        class="btn btn-ghost btn-xs text-error">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="addCriteria()" class="btn btn-outline btn-sm mt-4">
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
                        <x-ui.button type="primary" :isSubmit="true">
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
        <div class="lg:col-span-1 space-y-6">
            <x-ui.card title="Panduan">
                <div class="space-y-4">
                    <x-ui.alert type="info">
                        <div>
                            <p class="font-semibold">Tips Membuat Kriteria</p>
                            <ul class="text-sm mt-2 space-y-1">
                                <li>• Gunakan kriteria yang jelas dan terukur</li>
                                <li>• Bobot akan ditentukan melalui AHP Weighting</li>
                                <li>• Setiap kriteria sebaiknya memiliki sub-kriteria</li>
                                <li>• Kode akan di-generate otomatis jika dikosongkan</li>
                            </ul>
                        </div>
                    </x-ui.alert>

                    <div class="bg-base-200/50 p-3 rounded-lg">
                        <p class="text-sm font-semibold mb-2">Contoh Hierarki</p>
                        <p class="text-xs text-base-content/60 mb-2">Set: "Kriteria Penilaian" → Prefix: <span
                                class="font-mono font-bold">KRI</span></p>
                        <div class="text-sm space-y-1 text-base-content/70">
                            <p class="font-medium text-primary">🎯 KRI - Kriteria Penilaian (Goal)</p>
                            <p class="pl-4">📁 <span class="font-mono">KRI-1</span> Pedagogik</p>
                            <p class="pl-8 text-xs">📄 <span class="font-mono">KRI-1.1</span> Perencanaan Pembelajaran
                            </p>
                            <p class="pl-8 text-xs">📄 <span class="font-mono">KRI-1.2</span> Pelaksanaan Pembelajaran
                            </p>
                            <p class="pl-4">📁 <span class="font-mono">KRI-2</span> Kepribadian</p>
                            <p class="pl-8 text-xs">📄 <span class="font-mono">KRI-2.1</span> Integritas</p>
                            <p class="pl-8 text-xs">📄 <span class="font-mono">KRI-2.2</span> Etika</p>
                            <p class="pl-4">📁 <span class="font-mono">KRI-3</span> Sosial</p>
                            <p class="pl-4">📁 <span class="font-mono">KRI-4</span> Profesional</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Alur Selanjutnya">
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="badge badge-primary badge-sm mt-0.5">1</span>
                        <div class="text-sm">
                            <p class="font-medium">Buat Set Kriteria</p>
                            <p class="text-base-content/60">Anda sedang di sini</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="badge badge-ghost badge-sm mt-0.5">2</span>
                        <div class="text-sm">
                            <p class="font-medium">Atur Bobot AHP</p>
                            <p class="text-base-content/60">Perbandingan berpasangan antar kriteria</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="badge badge-ghost badge-sm mt-0.5">3</span>
                        <div class="text-sm">
                            <p class="font-medium">Buat Template KPI Form</p>
                            <p class="text-base-content/60">Form penilaian berdasarkan kriteria</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="badge badge-ghost badge-sm mt-0.5">4</span>
                        <div class="text-sm">
                            <p class="font-medium">Mulai Penilaian</p>
                            <p class="text-base-content/60">Assessor menilai guru</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>