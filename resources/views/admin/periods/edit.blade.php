<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.periods.index') }}">Periode Penilaian</a></li>
        <li>Edit Periode</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Periode Penilaian</h1>
                <p class="text-base-content/60">Edit periode penilaian yang sudah ada</p>
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

                <form method="POST" action="{{ route('admin.periods.update', $period) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Info -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Periode</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-ui.input name="name" label="Nama Periode"
                                    placeholder="Contoh: Semester Ganjil 2025/2026" :value="old('name', $period->name)"
                                    required />
                            </div>
                            <x-ui.input name="academic_year" label="Tahun Ajaran" placeholder="2025/2026"
                                :value="old('academic_year', $period->academic_year)" />
                            <x-ui.select name="semester" label="Semester" :options="[
                                    'ganjil' => 'Ganjil',
                                    'genap' => 'Genap'
                                ]" :searchable="false" :value="old('semester', $period->semester)" />
                        </div>
                    </div>

                    <!-- Scoring Window -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Waktu Penilaian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input name="scoring_open_at" label="Tanggal Mulai" type="date"
                                :value="old('scoring_open_at', $period->scoring_open_at?->format('Y-m-d'))" />
                            <x-ui.input name="scoring_close_at" label="Tanggal Selesai" type="date"
                                :value="old('scoring_close_at', $period->scoring_close_at?->format('Y-m-d'))" />
                        </div>
                        <p class="text-sm text-base-content/60 mt-2">
                            Periode penilaian akan otomatis terbuka/tertutup sesuai tanggal yang ditentukan.
                        </p>
                    </div>

                    <!-- Criteria Set -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Set Kriteria</h3>
                        @php
                        $currentCriteriaSetId = old('criteria_set_id', $period->ahpModel?->criteria_set_id);
                        @endphp
                        <x-ui.select name="criteria_set_id" label="Pilih Set Kriteria" :options="$criteriaSets ?? []"
                            :value="$currentCriteriaSetId"
                            helpText="Set kriteria yang akan digunakan untuk penilaian" />
                    </div>

                    <!-- KPI Form -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Form KPI</h3>
                        @php
                        $currentKpiFormId = old('kpi_form_version_id', $period->assignments->first()?->form_version_id);
                        @endphp
                        <x-ui.select name="kpi_form_version_id" label="Pilih Form KPI" :options="$kpiForms ?? []"
                            :value="$currentKpiFormId" helpText="Form penilaian yang akan digunakan penilai" />
                    </div>

                    <!-- Description -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Deskripsi (Opsional)</h3>
                        <x-ui.textarea name="description" label="Deskripsi Periode"
                            placeholder="Deskripsi atau catatan tambahan tentang periode ini..." rows="3"
                            :value="old('description', $period->meta['description'] ?? '')" />
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
                            Update Periode
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-ui.card title="Status Periode">
                <div class="space-y-3">
                    @php
                    $statusInfo = match($period->status) {
                    'draft' => ['type' => 'ghost', 'text' => 'Draft', 'desc' => 'Belum aktif, masih bisa diedit'],
                    'open' => ['type' => 'success', 'text' => 'Open', 'desc' => 'Penilaian sedang berjalan'],
                    'closed' => ['type' => 'warning', 'text' => 'Closed', 'desc' => 'Penilaian selesai'],
                    'archived' => ['type' => 'neutral', 'text' => 'Archived', 'desc' => 'Data historis'],
                    default => ['type' => 'ghost', 'text' => $period->status, 'desc' => '']
                    };
                    @endphp

                    <div class="flex items-center gap-3">
                        <x-ui.badge :type="$statusInfo['type']">{{ $statusInfo['text'] }}</x-ui.badge>
                        <span class="text-sm text-base-content/60">{{ $statusInfo['desc'] }}</span>
                    </div>

                    @if($period->status === 'draft')
                    <x-ui.alert type="info" size="sm">
                        <strong>Status Draft:</strong> Periode ini masih dalam tahap penyusunan dan dapat diedit dengan
                        bebas.
                    </x-ui.alert>
                    @elseif($period->status === 'open')
                    <x-ui.alert type="warning" size="sm">
                        <strong>Status Open:</strong> Periode aktif. Perubahan dapat mempengaruhi penilaian yang sedang
                        berjalan.
                    </x-ui.alert>
                    @else
                    <x-ui.alert type="neutral" size="sm">
                        <strong>Status {{ ucfirst($period->status) }}:</strong> Periode tidak aktif. Hanya data tertentu
                        yang dapat diubah.
                    </x-ui.alert>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card title="Informasi" class="mt-6">
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="font-medium">Dibuat:</p>
                        <p class="text-base-content/60">{{ $period->created_at->format('d M Y H:i') }}</p>
                    </div>
                    @if($period->updated_at && $period->updated_at != $period->created_at)
                    <div>
                        <p class="font-medium">Terakhir diubah:</p>
                        <p class="text-base-content/60">{{ $period->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                    @if($period->ahpModel)
                    <div>
                        <p class="font-medium">AHP Model:</p>
                        <p class="text-base-content/60">
                            {{ $period->ahpModel->criteriaSet?->name ?? 'Tidak diatur' }}
                        </p>
                    </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
