<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Manajemen User</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Manajemen User</h1>
                <p class="text-base-content/60">Kelola akun Admin, Penilai, dan Guru</p>
            </div>
            <x-ui.button type="primary" href="{{ route('admin.users.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah User
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <x-ui.input
                    name="search"
                    placeholder="Cari nama atau email..."
                    :value="request('search')"
                />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select
                    name="role"
                    placeholder="Semua Role"
                    :options="[
                        'admin' => 'Admin',
                        'assessor' => 'Penilai',
                        'teacher' => 'Guru'
                    ]"
                    :value="request('role')"
                    :searchable="false"
                />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select
                    name="status"
                    placeholder="Semua Status"
                    :options="[
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif'
                    ]"
                    :value="request('status')"
                    :searchable="false"
                />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.users.index') }}">
                    Reset
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <!-- Users Table -->
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $user->name }}</div>
                                        <div class="text-sm opacity-50">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    @php
                                        $badgeType = match($role->key) {
                                            'admin' => 'error',
                                            'assessor' => 'warning',
                                            'teacher' => 'info',
                                            default => 'ghost'
                                        };
                                    @endphp
                                    <x-ui.badge :type="$badgeType" size="sm">{{ $role->name }}</x-ui.badge>
                                @endforeach
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <x-ui.badge type="success" size="sm">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge type="ghost" size="sm">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}
                            </td>
                            <td class="text-right">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('admin.users.show', $user) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat Detail
                                        </a></li>
                                        <li><a href="{{ route('admin.users.edit', $user) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a></li>
                                        <li><a onclick="document.getElementById('reset-password-{{ $user->id }}').showModal()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            Reset Password
                                        </a></li>
                                        <div class="divider my-1"></div>
                                        @if($user->status === 'active')
                                            <li><a onclick="document.getElementById('deactivate-{{ $user->id }}').showModal()" class="text-warning">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                Nonaktifkan
                                            </a></li>
                                        @else
                                            <li>
                                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-success w-full text-left">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Aktifkan
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li><a onclick="document.getElementById('delete-{{ $user->id }}').showModal()" class="text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </a></li>
                                    </ul>
                                </div>

                                <!-- Reset Password Modal -->
                                <x-ui.modal id="reset-password-{{ $user->id }}" title="Reset Password">
                                    <p>Anda yakin ingin mereset password untuk <strong>{{ $user->name }}</strong>?</p>
                                    <p class="text-sm text-base-content/60 mt-2">Password baru akan dikirim ke email user.</p>
                                    <x-slot:actions>
                                        <form method="dialog">
                                            <button class="btn btn-ghost">Batal</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                                            @csrf
                                            <x-ui.button type="warning">Reset Password</x-ui.button>
                                        </form>
                                    </x-slot:actions>
                                </x-ui.modal>

                                <!-- Deactivate Modal -->
                                <x-ui.modal id="deactivate-{{ $user->id }}" title="Nonaktifkan User">
                                    <p>Anda yakin ingin menonaktifkan <strong>{{ $user->name }}</strong>?</p>
                                    <p class="text-sm text-base-content/60 mt-2">User tidak akan bisa login sampai diaktifkan kembali.</p>
                                    <x-slot:actions>
                                        <form method="dialog">
                                            <button class="btn btn-ghost">Batal</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button type="warning">Nonaktifkan</x-ui.button>
                                        </form>
                                    </x-slot:actions>
                                </x-ui.modal>

                                <!-- Delete Modal -->
                                <x-ui.modal id="delete-{{ $user->id }}" title="Hapus User">
                                    <p>Anda yakin ingin menghapus <strong>{{ $user->name }}</strong>?</p>
                                    <p class="text-sm text-error mt-2">Tindakan ini tidak dapat dibatalkan!</p>
                                    <x-slot:actions>
                                        <form method="dialog">
                                            <button class="btn btn-ghost">Batal</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="error">Hapus</x-ui.button>
                                        </form>
                                    </x-slot:actions>
                                </x-ui.modal>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/60">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p>Belum ada user terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($users) && $users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.admin>
