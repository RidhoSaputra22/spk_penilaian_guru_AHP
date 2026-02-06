<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li>Edit - {{ $template->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Template</h1>
                <p class="text-base-content/60">Perbarui informasi template {{ $template->name }}</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.builder', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Form Builder
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.index') }}">
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
        <div class="lg:col-span-2 space-y-6">
            <!-- Edit Form -->
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

                <form method="POST" action="{{ route('admin.kpi-forms.update', $template) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="space-y-4">
                            <x-ui.input name="name" label="Nama Template"
                                placeholder="Contoh: Form Penilaian Kinerja Guru Semester 1"
                                value="{{ old('name', $template->name) }}" required />
                            <x-ui.textarea name="description" label="Deskripsi" rows="3"
                                placeholder="Deskripsi singkat tentang template form ini..."
                                value="{{ old('description', $template->description) }}" />
                        </div>
                    </div>

                    <!-- Hubungkan dengan Set Kriteria -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Hubungkan dengan Set Kriteria</h3>
                        <p class="text-sm text-base-content/60 mb-4">
                            Pilih Set Kriteria untuk menghubungkan form ini dengan bobot AHP.
                        </p>
                        @php
                            $criteriaSetOptions = $criteriaSets->mapWithKeys(fn($cs) => [$cs->id => $cs->name . ($cs->is_active ? ' (Aktif)' : '')])->toArray();
                        @endphp
                        <x-ui.select name="criteria_set_id" label="Set Kriteria (Opsional)" :options="$criteriaSetOptions" selected="{{ old('criteria_set_id', $template->criteria_set_id) }}" placeholder="-- Pilih Set Kriteria --" />
                    </div>

                    <x-ui.alert type="info">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Untuk mengubah struktur form (seksi & item), gunakan <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="link link-primary font-medium">Form Builder</a>.</span>
                    </x-ui.alert>

                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.index') }}">Batal</x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true">Simpan Perubahan</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Quick Actions -->
            <x-ui.card title="Aksi Cepat">
                <div class="flex flex-wrap gap-3">
                    <x-ui.button type="outline" href="{{ route('admin.kpi-forms.builder', $template) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Struktur Form
                    </x-ui.button>
                    <x-ui.button type="outline" href="{{ route('admin.kpi-forms.preview', $template) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview Form
                    </x-ui.button>
                    <x-ui.button type="outline" href="{{ route('admin.kpi-forms.versions', $template) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Versi
                    </x-ui.button>
                    <form method="POST" action="{{ route('admin.kpi-forms.clone', $template) }}" class="inline">
                        @csrf
                        <x-ui.button type="outline" :isSubmit="true"
                            onclick="return confirm('Duplikat template ini?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Duplikat Template
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <!-- Template Info -->
            <x-ui.card title="Informasi Template">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">Status</p>
                        @php
                            $latestVersion = $template->versions->first();
                            $statusBadge = match($latestVersion?->status ?? 'draft') {
                                'draft' => 'ghost',
                                'published' => 'success',
                                'archived' => 'neutral',
                                default => 'ghost'
                            };
                        @endphp
                        <x-ui.badge :type="$statusBadge">
                            {{ ucfirst($latestVersion?->status ?? 'Draft') }}
                        </x-ui.badge>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Versi Terbaru</p>
                        <p class="text-sm">v{{ $latestVersion?->version ?? '1.0' }}</p>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Jumlah Versi</p>
                        <p class="text-sm">{{ $template->versions->count() }} versi</p>
                    </div>

                    @if($latestVersion)
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Struktur Form</p>
                        <div class="flex gap-2 mt-2">
                            <x-ui.badge type="ghost" size="sm">
                                {{ $latestVersion->sections->count() ?? 0 }} seksi
                            </x-ui.badge>
                            <x-ui.badge type="ghost" size="sm">
                                {{ $latestVersion->sections->sum(fn($s) => $s->items->count()) ?? 0 }} item
                            </x-ui.badge>
                        </div>
                    </div>
                    @endif

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Dibuat</p>
                        <p class="text-sm">{{ $template->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Terakhir Diperbarui</p>
                        <p class="text-sm">{{ $template->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </x-ui.card>

            <!-- Danger Zone -->
            <x-ui.card title="Zona Berbahaya">
                <div class="space-y-4">
                    <x-ui.alert type="warning">
                        <p class="text-sm">Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.</p>
                    </x-ui.alert>
                    <form method="POST" action="{{ route('admin.kpi-forms.destroy', $template) }}"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini? Semua versi dan data terkait akan dihapus permanen.')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="error" class="w-full" :isSubmit="true">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Template
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
