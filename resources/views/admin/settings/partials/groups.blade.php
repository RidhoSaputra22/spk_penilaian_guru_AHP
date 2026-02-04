<x-ui.card title="Kelompok Guru">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-base-content/70">Kelola kelompok guru untuk penugasan penilaian</p>
        <x-ui.button type="primary" size="sm" onclick="add_group_modal.showModal()">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kelompok
        </x-ui.button>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Kelompok</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Guru</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teacherGroups as $group)
                    <tr>
                        <td class="font-semibold">{{ $group->name }}</td>
                        <td class="text-sm text-base-content/70">{{ $group->description ?? '-' }}</td>
                        <td>
                            <x-ui.badge variant="info">{{ $group->teachers_count }} guru</x-ui.badge>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <form action="{{ route('admin.settings.delete-teacher-group', $group) }}" method="POST"
                                    onsubmit="return confirm('Hapus kelompok ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost btn-square">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-base-content/70">
                            Belum ada kelompok guru
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

<!-- Add Group Modal -->
<dialog id="add_group_modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Tambah Kelompok Guru</h3>
        <form method="POST" action="{{ route('admin.settings.store-teacher-group') }}">
            @csrf
            <div class="space-y-4">
                <x-ui.input type="text" name="name" label="Nama Kelompok" required />
                <x-ui.textarea name="description" label="Deskripsi" rows="3" />
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="add_group_modal.close()">Batal</button>
                <x-ui.button type="primary" :isSubmit="true">Simpan</x-ui.button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
