@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold">Profil Saya</h1>
        <p class="text-base-content/70">Kelola informasi akun Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body items-center text-center">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24">
                            <span class="text-3xl">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        </div>
                    </div>
                    <h2 class="card-title">{{ auth()->user()->name }}</h2>
                    <p class="text-base-content/70">{{ auth()->user()->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach(auth()->user()->roles as $role)
                            <span class="badge badge-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                    <div class="divider"></div>
                    <div class="text-sm text-base-content/70 space-y-2 w-full text-left">
                        <div class="flex justify-between">
                            <span>Bergabung</span>
                            <span>{{ auth()->user()->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Institusi</span>
                            <span>{{ auth()->user()->institution->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Informasi Profil</h3>
                    <p class="text-sm text-base-content/70 mb-4">Perbarui nama dan alamat email akun Anda</p>

                    <form action="#" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Nama</span>
                            </label>
                            <input type="text" name="name" value="{{ auth()->user()->name }}" class="input input-bordered" required />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" name="email" value="{{ auth()->user()->email }}" class="input input-bordered" required />
                        </div>

                        <div class="card-actions justify-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Ubah Password</h3>
                    <p class="text-sm text-base-content/70 mb-4">Pastikan menggunakan password yang kuat dan unik</p>

                    <form action="#" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Password Saat Ini</span>
                            </label>
                            <input type="password" name="current_password" class="input input-bordered" required />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Password Baru</span>
                            </label>
                            <input type="password" name="password" class="input input-bordered" required />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Konfirmasi Password Baru</span>
                            </label>
                            <input type="password" name="password_confirmation" class="input input-bordered" required />
                        </div>

                        <div class="card-actions justify-end">
                            <button type="submit" class="btn btn-primary">Ubah Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card bg-base-100 shadow-sm border border-error/20">
                <div class="card-body">
                    <h3 class="card-title text-lg text-error">Zona Bahaya</h3>
                    <p class="text-sm text-base-content/70 mb-4">
                        Setelah akun dihapus, semua data akan dihapus secara permanen. Harap pertimbangkan dengan hati-hati sebelum melanjutkan.
                    </p>

                    <button class="btn btn-error btn-outline" onclick="document.getElementById('deleteAccountModal').showModal()">
                        Hapus Akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<dialog id="deleteAccountModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">Hapus Akun</h3>
        <p class="py-4">Apakah Anda yakin ingin menghapus akun? Semua data Anda akan dihapus secara permanen.</p>
        <form action="#" method="POST">
            @csrf
            @method('DELETE')
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Masukkan password untuk konfirmasi</span>
                </label>
                <input type="password" name="password" class="input input-bordered" required />
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('deleteAccountModal').close()">Batal</button>
                <button type="submit" class="btn btn-error">Ya, Hapus Akun</button>
            </div>
        </form>
    </div>
</dialog>
@endsection
