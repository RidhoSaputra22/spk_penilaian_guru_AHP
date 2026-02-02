@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Edit User</h1>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Edit User: {{ $user->name }}</h5>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                        </div>

                        <!-- Simple roles - just to satisfy validation -->
                        <input type="hidden" name="roles[]" value="1">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
        <div class="lg:col-span-2">
            <x-ui.card>
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Info -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                name="name"
                                label="Nama Lengkap"
                                :value="$user->name"
                                required
                            />
                            <x-ui.input
                                name="email"
                                label="Email"
                                type="email"
                                :value="$user->email"
                                required
                            />
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Role & Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select
                                name="role"
                                label="Role"
                                :options="[
                                    'admin' => 'Admin',
                                    'assessor' => 'Penilai (Assessor)',
                                    'teacher' => 'Guru (Teacher)'
                                ]"
                                :value="$user->roles->first()?->key"
                                required
                            />
                            <x-ui.select
                                name="status"
                                label="Status"
                                :options="[
                                    'active' => 'Aktif',
                                    'inactive' => 'Nonaktif'
                                ]"
                                :value="$user->status"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <!-- Password (Optional) -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Ubah Password</h3>
                        <p class="text-sm text-base-content/60 mb-4">Kosongkan jika tidak ingin mengubah password.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                name="password"
                                label="Password Baru"
                                type="password"
                                placeholder="Min. 8 karakter"
                            />
                            <x-ui.input
                                name="password_confirmation"
                                label="Konfirmasi Password"
                                type="password"
                                placeholder="Ulangi password"
                            />
                        </div>
                    </div>

                    <!-- Additional Info (Dynamic based on role) -->
                    @if($user->hasRole('teacher') && $user->teacherProfile)
                        <div class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Profil Guru</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.input
                                    name="nip"
                                    label="NIP"
                                    :value="$user->teacherProfile->nip"
                                />
                                <x-ui.input
                                    name="nuptk"
                                    label="NUPTK"
                                    :value="$user->teacherProfile->nuptk"
                                />
                                <x-ui.select
                                    name="teacher_group_id"
                                    label="Kelompok Guru"
                                    :options="$teacherGroups ?? []"
                                    :value="$user->teacherProfile->teacher_group_id"
                                />
                                <x-ui.input
                                    name="subject"
                                    label="Mata Pelajaran"
                                    :value="$user->teacherProfile->subject"
                                />
                            </div>
                        </div>
                    @endif

                    @if($user->hasRole('assessor') && $user->assessorProfile)
                        <div class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Profil Penilai</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.input
                                    name="assessor_position"
                                    label="Jabatan"
                                    :value="$user->assessorProfile->position"
                                />
                                <x-ui.input
                                    name="assessor_nip"
                                    label="NIP"
                                    :value="$user->assessorProfile->nip"
                                />
                            </div>
                        </div>
                    @endif

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.users.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1 space-y-6">
            <!-- User Info Card -->
            <x-ui.card>
                <div class="flex items-center gap-4 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-16">
                            <span class="text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ $user->name }}</h3>
                        <p class="text-base-content/60 text-sm">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">ID</span>
                        <span class="font-mono text-xs">{{ $user->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Role</span>
                        <span>
                            @foreach($user->roles as $role)
                                <x-ui.badge type="{{ $role->key === 'admin' ? 'error' : ($role->key === 'assessor' ? 'warning' : 'info') }}" size="sm">
                                    {{ $role->name }}
                                </x-ui.badge>
                            @endforeach
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Status</span>
                        <x-ui.badge :type="$user->status === 'active' ? 'success' : 'ghost'" size="sm">
                            {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-ui.badge>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Terdaftar</span>
                        <span>{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Login Terakhir</span>
                        <span>{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Quick Actions -->
            <x-ui.card title="Aksi Cepat">
                <div class="space-y-2">
                    <button onclick="document.getElementById('reset-password').showModal()" class="btn btn-outline btn-warning btn-sm btn-block justify-start">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Reset Password
                    </button>
                    @if($user->status === 'active')
                        <button onclick="document.getElementById('deactivate').showModal()" class="btn btn-outline btn-sm btn-block justify-start">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Nonaktifkan User
                        </button>
                    @else
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline btn-success btn-sm btn-block justify-start">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aktifkan User
                            </button>
                        </form>
                    @endif
                    <button onclick="document.getElementById('delete').showModal()" class="btn btn-outline btn-error btn-sm btn-block justify-start">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus User
                    </button>
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Modals -->
    <x-ui.modal id="reset-password" title="Reset Password">
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

    <x-ui.modal id="deactivate" title="Nonaktifkan User">
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

    <x-ui.modal id="delete" title="Hapus User">
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
</x-layouts.admin>
