@extends('layouts.admin')

@section('title', 'Detail Pengguna - ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold">Detail Pengguna</h1>
            <p class="text-base-content/70">Informasi lengkap pengguna {{ $user->name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
        </div>
    </div>

    <!-- User Profile -->
    <x-ui.card title="Profil Pengguna">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content w-24 h-24 rounded-full">
                        <span class="text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Nama Lengkap</span>
                    </label>
                    <div class="text-lg font-semibold">{{ $user->name }}</div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <div class="text-lg">{{ $user->email }}</div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Status</span>
                    </label>
                    @if($user->deactivated_at)
                        <x-ui.badge variant="error">Nonaktif</x-ui.badge>
                    @else
                        <x-ui.badge variant="success">Aktif</x-ui.badge>
                    @endif
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Terakhir Login</span>
                    </label>
                    <div class="text-sm">
                        {{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Belum pernah login' }}
                    </div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Terdaftar</span>
                    </label>
                    <div class="text-sm">{{ $user->created_at->format('d M Y H:i') }}</div>
                </div>

                <div>
                    <label class="label">
                        <span class="label-text font-semibold">ID</span>
                    </label>
                    <div class="text-sm font-mono">{{ $user->id }}</div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Actions -->
    <x-ui.card title="Tindakan">
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                @csrf
                <x-ui.button variant="outline" type="submit" onclick="return confirm('Yakin reset password pengguna ini?')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Reset Password
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                @csrf
                <x-ui.button variant="{{ $user->deactivated_at ? 'success' : 'warning' }}" type="submit" onclick="return confirm('Yakin mengubah status pengguna ini?')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    {{ $user->deactivated_at ? 'Aktifkan' : 'Nonaktifkan' }}
                </x-ui.button>
            </form>

            <button onclick="deleteModal.showModal()" class="btn btn-error btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Pengguna
            </button>
        </div>
    </x-ui.card>
</div>

<!-- Delete Modal -->
<x-ui.modal id="deleteModal" title="Hapus Pengguna">
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="space-y-4">
        @csrf
        @method('DELETE')
        <x-ui.alert type="warning">
            <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
        </x-ui.alert>
        <p>Apakah Anda yakin ingin menghapus pengguna <strong>{{ $user->name }}</strong>?</p>

        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="deleteModal.close()">
                Batal
            </x-ui.button>
            <x-ui.button variant="error" type="submit">
                Hapus
            </x-ui.button>
        </x-slot>
    </form>
</x-ui.modal>
@endsection
