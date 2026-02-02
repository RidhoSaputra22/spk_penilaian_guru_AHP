@extends('layouts.admin')

@section('title', 'Skala Penilaian')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold">Skala Penilaian</h1>
            <p class="text-base-content/70 mt-1">Kelola skala penilaian untuk penilaian guru</p>
        </div>
        <x-ui.button variant="primary" size="sm" onclick="createScaleModal.showModal()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Skala
        </x-ui.button>
    </div>

    <!-- Search and Filters -->
    <x-ui.card>
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <x-ui.input
                    type="search"
                    placeholder="Cari skala penilaian..."
                    name="search"
                    value="{{ request('search') }}"
                />
            </div>
            <div class="flex gap-2">
                <x-ui.button variant="outline" size="sm" type="submit">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </x-ui.button>
                <x-ui.button variant="ghost" size="sm" onclick="window.location.href = '{{ route('admin.scoring-scales.index') }}'">
                    Reset
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <!-- Scoring Scales Table -->
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Skala</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Opsi</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse([] as $key => $scale)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="font-semibold">{{ $scale->name ?? 'Sample Scale' }}</div>
                            </td>
                            <td>
                                <div class="text-sm text-base-content/70">
                                    {{ Str::limit($scale->description ?? 'Deskripsi skala penilaian', 50) }}
                                </div>
                            </td>
                            <td>
                                <x-ui.badge variant="info">
                                    {{ $scale->options_count ?? '5' }} Opsi
                                </x-ui.badge>
                            </td>
                            <td>
                                <x-ui.badge variant="{{ ($scale->is_active ?? true) ? 'success' : 'error' }}">
                                    {{ ($scale->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="text-sm">
                                    {{ $scale->created_at?->format('d M Y') ?? now()->format('d M Y') }}
                                </div>
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                                        <li><a href="#" onclick="editScaleModal.showModal()">Edit</a></li>
                                        <li><a href="#" onclick="viewOptionsModal.showModal()">Lihat Opsi</a></li>
                                        <li><a class="text-error" onclick="deleteScaleModal.showModal()">Hapus</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <div class="text-center">
                                        <p class="font-semibold">Belum ada skala penilaian</p>
                                        <p class="text-sm text-base-content/70">Tambah skala penilaian pertama Anda</p>
                                    </div>
                                    <x-ui.button variant="primary" size="sm" onclick="createScaleModal.showModal()">
                                        Tambah Skala
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>

<!-- Create Scale Modal -->
<x-ui.modal id="createScaleModal" title="Tambah Skala Penilaian">
    <form method="POST" action="#" class="space-y-4">
        @csrf
        <div>
            <label class="label">
                <span class="label-text font-semibold">Nama Skala <span class="text-error">*</span></span>
            </label>
            <x-ui.input
                type="text"
                name="name"
                placeholder="Masukkan nama skala..."
                required
            />
        </div>

        <div>
            <label class="label">
                <span class="label-text font-semibold">Deskripsi</span>
            </label>
            <x-ui.textarea
                name="description"
                rows="3"
                placeholder="Masukkan deskripsi skala..."
            />
        </div>

        <div>
            <label class="label">
                <span class="label-text font-semibold">Jumlah Opsi <span class="text-error">*</span></span>
            </label>
            <x-ui.select name="options_count" required>
                <option value="">Pilih jumlah opsi</option>
                <option value="3">3 Opsi</option>
                <option value="4">4 Opsi</option>
                <option value="5" selected>5 Opsi</option>
                <option value="6">6 Opsi</option>
                <option value="7">7 Opsi</option>
                <option value="10">10 Opsi</option>
            </x-ui.select>
        </div>

        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="createScaleModal.close()">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="submit">
                Simpan
            </x-ui.button>
        </x-slot>
    </form>
</x-ui.modal>

<!-- Edit Scale Modal -->
<x-ui.modal id="editScaleModal" title="Edit Skala Penilaian">
    <form method="POST" action="#" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="label">
                <span class="label-text font-semibold">Nama Skala <span class="text-error">*</span></span>
            </label>
            <x-ui.input
                type="text"
                name="name"
                value="Sample Scale"
                placeholder="Masukkan nama skala..."
                required
            />
        </div>

        <div>
            <label class="label">
                <span class="label-text font-semibold">Deskripsi</span>
            </label>
            <x-ui.textarea
                name="description"
                rows="3"
                placeholder="Masukkan deskripsi skala..."
            >Deskripsi skala penilaian</x-ui.textarea>
        </div>

        <div>
            <label class="label">
                <span class="label-text font-semibold">Status</span>
            </label>
            <x-ui.checkbox name="is_active" checked>
                Aktif
            </x-ui.checkbox>
        </div>

        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="editScaleModal.close()">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="submit">
                Update
            </x-ui.button>
        </x-slot>
    </form>
</x-ui.modal>

<!-- Delete Scale Modal -->
<x-ui.modal id="deleteScaleModal" title="Hapus Skala Penilaian">
    <form method="POST" action="#" class="space-y-4">
        @csrf
        @method('DELETE')
        <x-ui.alert type="warning">
            <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan. Skala penilaian akan dihapus secara permanen.
        </x-ui.alert>
        <p>Apakah Anda yakin ingin menghapus skala penilaian ini?</p>

        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="deleteScaleModal.close()">
                Batal
            </x-ui.button>
            <x-ui.button variant="error" type="submit">
                Hapus
            </x-ui.button>
        </x-slot>
    </form>
</x-ui.modal>

<!-- View Options Modal -->
<x-ui.modal id="viewOptionsModal" title="Opsi Skala Penilaian">
    <div class="space-y-4">
        <div class="grid gap-2">
            @for($i = 1; $i <= 5; $i++)
                <div class="flex justify-between items-center p-3 bg-base-200 rounded-lg">
                    <div>
                        <div class="font-semibold">Opsi {{ $i }}</div>
                        <div class="text-sm text-base-content/70">Deskripsi opsi {{ $i }}</div>
                    </div>
                    <x-ui.badge variant="info">
                        Nilai: {{ $i }}
                    </x-ui.badge>
                </div>
            @endfor
        </div>
    </div>
    <x-slot name="actions">
        <x-ui.button variant="outline" type="button" onclick="viewOptionsModal.close()">
            Tutup
        </x-ui.button>
    </x-slot>
</x-ui.modal>
@endsection
