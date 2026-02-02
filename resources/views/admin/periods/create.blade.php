<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.periods.index') }}">Periode Penilaian</a></li>
        <li>Tambah Periode</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah Periode Penilaian</h1>
                <p class="text-base-content/60">Buat periode penilaian baru</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.periods.index') }}">
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
                <form method="POST" action="{{ route('admin.periods.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Info -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Periode</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-ui.input name="name" label="Nama Periode"
                                    placeholder="Contoh: Semester Ganjil 2025/2026" required />
                            </div>
                            <x-ui.input name="academic_year" label="Tahun Ajaran" placeholder="2025/2026" />
                            <x-ui.select name="semester" label="Semester" :options="[
                                    'ganjil' => 'Ganjil',
                                    'genap' => 'Genap'
                                ]" :searchable="false" />
                        </div>
                    </div>

                    <!-- Scoring Window -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Waktu Penilaian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input name="scoring_open_at" label="Tanggal Mulai" type="date" />
                            <x-ui.input name="scoring_close_at" label="Tanggal Selesai" type="date" />
                        </div>
                        <p class="text-sm text-base-content/60 mt-2">
                            Periode penilaian akan otomatis terbuka/tertutup sesuai tanggal yang ditentukan.
                        </p>
                    </div>

                    <!-- Criteria Set -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Set Kriteria</h3>
                        <x-ui.select name="criteria_set_id" label="Pilih Set Kriteria" :options="$criteriaSets ?? []"
                            helpText="Set kriteria yang akan digunakan untuk penilaian" />
                    </div>

                    <!-- KPI Form -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Form KPI</h3>
                        <x-ui.select name="kpi_form_version_id" label="Pilih Form KPI" :options="$kpiForms ?? []"
                            helpText="Form penilaian yang akan digunakan penilai" />
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.periods.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Periode
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-ui.card title="Panduan">
                <div class="space-y-4 text-sm">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                            <span class="font-bold">1</span>
                        </div>
                        <div>
                            <p class="font-medium">Buat Periode</p>
                            <p class="text-base-content/60">Tentukan nama dan waktu penilaian.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                            <span class="font-bold">2</span>
                        </div>
                        <div>
                            <p class="font-medium">Atur Bobot AHP</p>
                            <p class="text-base-content/60">Lakukan pairwise comparison untuk menentukan bobot kriteria.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                            <span class="font-bold">3</span>
                        </div>
                        <div>
                            <p class="font-medium">Tugaskan Penilai</p>
                            <p class="text-base-content/60">Tentukan penilai dan guru yang akan dinilai.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                            <span class="font-bold">4</span>
                        </div>
                        <div>
                            <p class="font-medium">Buka Periode</p>
                            <p class="text-base-content/60">Penilai dapat mulai menilai guru.</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Status Periode" class="mt-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <x-ui.badge type="ghost">Draft</x-ui.badge>
                        <span class="text-sm text-base-content/60">Belum aktif, bisa diedit</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.badge type="success">Open</x-ui.badge>
                        <span class="text-sm text-base-content/60">Penilaian sedang berjalan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.badge type="warning">Closed</x-ui.badge>
                        <span class="text-sm text-base-content/60">Penilaian selesai</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.badge type="neutral">Archived</x-ui.badge>
                        <span class="text-sm text-base-content/60">Data historis</span>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
