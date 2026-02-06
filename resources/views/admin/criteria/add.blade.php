<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.criteria.index') }}">Kriteria</a></li>
        <li>Tambah Kriteria</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah Kriteria</h1>
                <p class="text-base-content/60">
                    @if(request('parent_id'))
                    Tambah sub-kriteria untuk {{ $parentNode->name ?? 'kriteria induk' }}
                    @else
                    Tambah kriteria baru ke set {{ $currentSet->name ?? '' }}
                    @endif
                </p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.criteria.index', ['set' => $currentSet->id ?? '']) }}">
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

                @if(request('parent_id') && isset($parentNode))
                <x-ui.alert type="info" class="mb-6">
                    <div>
                        <p class="font-semibold">Sub-kriteria dari:</p>
                        <p>{{ $parentNode->name }}</p>
                    </div>
                </x-ui.alert>
                @endif

                <form method="POST" action="{{ route('admin.criteria.store-node') }}" class="space-y-6">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="criteria_set_id" value="{{ $currentSet->id ?? '' }}">
                    <input type="hidden" name="parent_id" value="{{ $parentNode->id ?? '' }}">

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Kriteria</h3>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-sm text-base-content/60">Tipe:</span>
                            @if(request('parent_id') && isset($parentNode))
                                <span class="badge badge-secondary">Sub-kriteria</span>
                                <span class="text-sm text-base-content/60">dari {{ $parentNode->name }}</span>
                            @else
                                <span class="badge badge-primary">Kriteria</span>
                            @endif
                        </div>
                        <x-ui.alert type="info" class="mb-4">
                            <span class="text-sm">Kode akan di-generate otomatis berdasarkan nama set kriteria.</span>
                        </x-ui.alert>
                        <div>
                            <x-ui.input name="name" label="Nama" placeholder="Nama kriteria" value="{{ old('name') }}"
                                required />
                        </div>
                    </div>

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Deskripsi</h3>
                        <x-ui.textarea name="description" label="Deskripsi Kriteria"
                            placeholder="Deskripsi kriteria (opsional)" value="{{ old('description') }}" rows="4" />
                    </div>



                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost"
                            href="{{ route('admin.criteria.index', ['set' => $currentSet->id ?? '']) }}">Batal
                        </x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true">Simpan Kriteria</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card title="Informasi">
                <div class="space-y-4">
                    @if(isset($currentSet))
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">Set Kriteria</p>
                        <p class="text-sm">{{ $currentSet->name }}</p>
                        <p class="text-sm text-base-content/60">Versi {{ $currentSet->version }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            @if($currentSet->is_active)
                            <span class="badge badge-success badge-sm">Aktif</span>
                            @else
                            <span class="badge badge-ghost badge-sm">Tidak Aktif</span>
                            @endif
                            @if($currentSet->locked_at)
                            <span class="badge badge-warning badge-sm">Terkunci</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(request('parent_id') && isset($parentNode))
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Parent Kriteria</p>
                        <p class="text-sm">{{ $parentNode->name }}</p>
                        @if($parentNode->code)
                        <span class="badge badge-primary badge-sm mt-1">{{ $parentNode->code }}</span>
                        @endif
                    </div>
                    @endif

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Panduan</p>
                        <ul class="text-sm space-y-1 text-base-content/70">
                            <li>• <strong>Kriteria:</strong> Level tertinggi dalam hierarki</li>
                            <li>• <strong>Sub-kriteria:</strong> Turunan dari kriteria utama</li>
                            <li>• <strong>Indikator:</strong> Parameter pengukuran spesifik</li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Tips</p>
                            <p class="text-sm">Gunakan kode yang konsisten untuk memudahkan identifikasi kriteria
                                (misal: C1, C1.1, C1.1.1)</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
