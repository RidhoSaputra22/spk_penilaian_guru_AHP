@extends('layouts.admin')

@section('title', 'Edit Pengguna - ' . $user->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold">Edit Pengguna</h1>
            <p class="text-base-content/70">Perbarui informasi pengguna {{ $user->name }}</p>
        </div>
    </div>

    <!-- Edit Form -->
    <x-ui.card title="Form Edit Pengguna">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="name" class="label">
                        <span class="label-text font-semibold">Nama Lengkap</span>
                    </label>
                    <x-ui.input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Masukkan nama lengkap"
                        required
                        :error="$errors->get('name')"
                    />
                </div>

                <div class="space-y-2">
                    <label for="email" class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <x-ui.input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        placeholder="user@example.com"
                        required
                        :error="$errors->get('email')"
                    />
                </div>

                <div class="space-y-2">
                    <label for="password" class="label">
                        <span class="label-text font-semibold">Password Baru</span>
                        <span class="label-text-alt">(Kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <x-ui.input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password baru"
                        :error="$errors->get('password')"
                    />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="label">
                        <span class="label-text font-semibold">Konfirmasi Password</span>
                    </label>
                    <x-ui.input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Konfirmasi password baru"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <label class="label">
                    <span class="label-text font-semibold">Status</span>
                </label>
                <div class="form-control">
                    <label class="label cursor-pointer">
                        <span class="label-text">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pengguna Aktif
                        </span>
                        <x-ui.checkbox
                            name="is_active"
                            value="1"
                            :checked="!$user->deactivated_at"
                        />
                    </label>
                </div>
            </div>

            <!-- Hidden roles field for validation -->
            <input type="hidden" name="roles[]" value="1">

            <div class="divider"></div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>
                <x-ui.button variant="primary" type="submit">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Pengguna
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
@endsection
