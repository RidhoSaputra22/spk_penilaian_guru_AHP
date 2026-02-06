<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li>Buat Template Baru</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Buat Template Form KPI</h1>
                <p class="text-base-content/60">Buat template form penilaian KPI baru</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.index') }}">
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

                <form method="POST" action="{{ route('admin.kpi-forms.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Information -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Nama Template"
                                placeholder="Contoh: Form Penilaian Kinerja Guru Semester 1" value="{{ old('name') }}"
                                required />
                            <x-ui.textarea name="description" label="Deskripsi" rows="3"
                                placeholder="Deskripsi singkat tentang template form ini..."
                                value="{{ old('description') }}" />
                        </div>
                    </div>

                    <!-- Version Settings -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Pengaturan Versi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select name="status" label="Status" :options="[
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ]" selected="{{ old('status', 'draft') }}" required />
                            <x-ui.input name="version" label="Versi" placeholder="1.0"
                                value="{{ old('version', '1.0') }}" />
                        </div>
                    </div>

                    <!-- Hubungkan dengan Set Kriteria -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Hubungkan dengan Set Kriteria</h3>
                        <p class="text-sm text-base-content/60 mb-4">
                            Pilih Set Kriteria untuk menghubungkan form ini dengan bobot AHP. Setelah terhubung, Anda
                            dapat otomatis membuat seksi dan item dari hierarki kriteria.
                        </p>
                        @php
                            $criteriaSetOptions = $criteriaSets->mapWithKeys(fn($cs) => [$cs->id => $cs->name . ($cs->is_active ? ' (Aktif)' : '')])->toArray();
                        @endphp
                        <x-ui.select name="criteria_set_id" label="Set Kriteria (Opsional)" :options="$criteriaSetOptions" selected="{{ old('criteria_set_id') }}" placeholder="-- Pilih Set Kriteria --" />
                    </div>



                    <x-ui.alert type="info" class="mb-6">

                        <span>Setelah membuat template, Anda dapat menggunakan <strong>Form Builder</strong> untuk
                            menambahkan seksi dan item penilaian.</span>
                    </x-ui.alert>

                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.index') }}">Batal</x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true">Buat Template</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card title="Panduan">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-2">Tentang Template</p>
                        <p class="text-sm text-base-content/70">
                            Template Form KPI adalah struktur dasar form penilaian yang dapat digunakan berulang kali
                            untuk periode penilaian yang berbeda.
                        </p>
                    </div>

                    <div class="bg-warning/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-warning mb-2">Status Template</p>
                        <ul class="text-sm space-y-1 text-base-content/70">
                            <li>• <strong>Draft:</strong> Masih dalam pengembangan</li>
                            <li>• <strong>Published:</strong> Siap digunakan</li>
                        </ul>
                    </div>

                    <div class="bg-success/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-success mb-2">Langkah Selanjutnya</p>
                        <ol class="text-sm space-y-1 text-base-content/70 list-decimal list-inside">
                            <li>Buat template dasar</li>
                            <li>Gunakan Form Builder</li>
                            <li>Tambahkan seksi dan item</li>
                            <li>Publish template</li>
                        </ol>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
