<x-layouts.admin>

    <div class="space-y-6">


        @if($errors->any())
        <x-ui.alert type="error">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
        @endif

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Manajemen Pengguna</h1>
                <p class="text-base-content/70 mt-1">Kelola akun pengguna sistem</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pengguna
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.stat title="Total Pengguna" value="{{ $users->total() }}" description="Semua pengguna"
                icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <x-ui.stat title="Admin"
                value="{{ $users->filter(fn($u) => $u->roles->contains('key', 'admin'))->count() }}"
                description="Administrator"
                icon="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                class="text-primary" />
            <x-ui.stat title="Penilai"
                value="{{ $users->filter(fn($u) => $u->roles->contains('key', 'assessor'))->count() }}"
                description="Assessor"
                icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                class="text-info" />
            <x-ui.stat title="Guru"
                value="{{ $users->filter(fn($u) => $u->roles->contains('key', 'teacher'))->count() }}"
                description="Teacher"
                icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                class="text-success" />
        </div>

        <!-- Filters -->
        <x-ui.card>
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <x-ui.input type="search" placeholder="Cari nama atau email..." name="search"
                        value="{{ request('search') }}" />
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="role" :options="[
                    '' => 'Semua Role',
                    'admin' => 'Admin',
                    'assessor' => 'Penilai',
                    'teacher' => 'Guru'
                ]" selected="{{ request('role') }}" />
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="status" :options="[
                    '' => 'Semua Status',
                    'active' => 'Aktif',
                    'inactive' => 'Nonaktif'
                ]" selected="{{ request('status') }}" />
                </div>
                <div class="flex gap-2">
                    <x-ui.button variant="outline" size="sm" type="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari
                    </x-ui.button>
                    <x-ui.button variant="ghost" size="sm"
                        onclick="window.location.href = '{{ route('admin.users.index') }}'">
                        Reset
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Users Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terakhir Login</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-10 rounded-full">
                                            <span class="text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $user->name }}</div>
                                        <div class="text-sm text-base-content/70">ID:
                                            {{ Str::limit($user->id, 8, '...') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $user->email }}</div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                    <x-ui.badge
                                        variant="{{ $role->key === 'admin' ? 'primary' : ($role->key === 'assessor' ? 'info' : 'success') }}">
                                        {{ $role->name }}
                                    </x-ui.badge>
                                    @empty
                                    <span class="text-sm text-base-content/60">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                @if($user->status === 'inactive')
                                <x-ui.badge variant="error">Nonaktif</x-ui.badge>
                                @else
                                <x-ui.badge variant="success">Aktif</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-xs"
                                        title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-xs"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <div class="dropdown dropdown-end ">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 5v.01M12 12v.01M12 19v.01" />
                                            </svg>
                                        </label>
                                        <ul tabindex="0"
                                            class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box min-w-sm">

                                            <li>
                                                <button type="submit"
                                                    onclick="toggleActiveModal{{ str_replace('-', '', $user->id) }}.showModal()"
                                                    class="w-full text-left {{ $user->status === 'inactive' ? 'text-success' : 'text-warning' }} flex">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $user->status === 'inactive' ? 'Aktifkan' : 'Nonaktifkan' }}
                                                </button>
                                            </li>

                                            <li>
                                                <button type="button" class="text-error w-full text-left flex"
                                                    onclick="deleteModal{{ str_replace('-', '', $user->id) }}.showModal()">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <div class="text-center">
                                        <p class="font-semibold">Belum ada pengguna</p>
                                        <p class="text-sm text-base-content/70">Tambahkan pengguna pertama Anda</p>
                                    </div>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                        Tambah Pengguna
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="flex justify-end mt-4">
                {{ $users->links() }}
            </div>
            @endif
        </x-ui.card>

        <!-- Delete Modals for each user -->
        @foreach($users as $user)
        <dialog id="deleteModal{{ str_replace('-', '', $user->id) }}" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Hapus Pengguna</h3>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="alert alert-warning">
                        <svg class="stroke-current shrink-0 w-6 h-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z">
                            </path>
                        </svg>
                        <span><strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.</span>
                    </div>
                    <p>Apakah Anda yakin ingin menghapus pengguna <strong>{{ $user->name }}</strong>?</p>

                    <div class="modal-action">
                        <button type="button" class="btn"
                            onclick="deleteModal{{ str_replace('-', '', $user->id) }}.close()">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-error">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        <dialog id="toggleActiveModal{{ str_replace('-', '', $user->id) }}" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ $user->status === 'active' ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna' }}</h3>
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="space-y-4">
                    @csrf


                    <p>Apakah Anda yakin ingin {{ $user->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }}
                        pengguna
                        <strong>{{ $user->name }}</strong>?
                    </p>

                    <div class="modal-action">
                        <button type="button" class="btn"
                            onclick="toggleActiveModal{{ str_replace('-', '', $user->id) }}.close()">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </form>
            </div>
        </dialog>
        @endforeach
    </div>

</x-layouts.admin>
