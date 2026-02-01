@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold">Pengaturan</h1>
        <p class="text-base-content/70">Konfigurasi sistem penilaian</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Menu -->
        <div class="lg:col-span-1">
            <ul class="menu bg-base-100 rounded-box shadow-sm w-full">
                <li class="menu-title">Umum</li>
                <li><a class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Institusi
                </a></li>
                <li><a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Skala Penilaian
                </a></li>
                <li><a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Kelompok Guru
                </a></li>
                <li class="menu-title">Sistem</li>
                <li><a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                </a></li>
                <li><a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifikasi
                </a></li>
                <li><a>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    Backup
                </a></li>
            </ul>
        </div>

        <!-- Content -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Institution Settings -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Informasi Institusi</h3>
                    <p class="text-sm text-base-content/70 mb-4">Pengaturan dasar institusi Anda</p>

                    <form action="#" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control md:col-span-2">
                                <label class="label">
                                    <span class="label-text">Nama Institusi</span>
                                </label>
                                <input type="text" name="name" value="{{ auth()->user()->institution->name ?? '' }}" class="input input-bordered" required />
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">NPSN</span>
                                </label>
                                <input type="text" name="npsn" value="{{ auth()->user()->institution->npsn ?? '' }}" class="input input-bordered" />
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Akreditasi</span>
                                </label>
                                <select name="accreditation" class="select select-bordered">
                                    <option value="">Pilih Akreditasi</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="Unaccredited">Belum Terakreditasi</option>
                                </select>
                            </div>

                            <div class="form-control md:col-span-2">
                                <label class="label">
                                    <span class="label-text">Alamat</span>
                                </label>
                                <textarea name="address" class="textarea textarea-bordered" rows="2">{{ auth()->user()->institution->address ?? '' }}</textarea>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Telepon</span>
                                </label>
                                <input type="text" name="phone" value="{{ auth()->user()->institution->phone ?? '' }}" class="input input-bordered" />
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Email</span>
                                </label>
                                <input type="email" name="email" value="{{ auth()->user()->institution->email ?? '' }}" class="input input-bordered" />
                            </div>
                        </div>

                        <div class="card-actions justify-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grading Settings -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Pengaturan Grade</h3>
                    <p class="text-sm text-base-content/70 mb-4">Konfigurasi rentang nilai untuk setiap grade</p>

                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Skor Minimum</th>
                                    <th>Skor Maksimum</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-success badge-lg">A</span></td>
                                    <td><input type="number" value="90" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="number" value="100" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="text" value="Sangat Baik" class="input input-bordered input-sm" /></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-info badge-lg">B</span></td>
                                    <td><input type="number" value="80" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="number" value="89" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="text" value="Baik" class="input input-bordered input-sm" /></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-warning badge-lg">C</span></td>
                                    <td><input type="number" value="70" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="number" value="79" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="text" value="Cukup" class="input input-bordered input-sm" /></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-error badge-lg">D</span></td>
                                    <td><input type="number" value="60" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="number" value="69" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="text" value="Kurang" class="input input-bordered input-sm" /></td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-neutral badge-lg">E</span></td>
                                    <td><input type="number" value="0" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="number" value="59" class="input input-bordered input-sm w-24" /></td>
                                    <td><input type="text" value="Sangat Kurang" class="input input-bordered input-sm" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <button type="button" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </div>

            <!-- AHP Settings -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg">Pengaturan AHP</h3>
                    <p class="text-sm text-base-content/70 mb-4">Konfigurasi metode Analytic Hierarchy Process</p>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Batas Consistency Ratio (CR)</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="number" step="0.01" value="0.10" class="input input-bordered w-32" />
                            <span class="text-sm text-base-content/70">Nilai CR harus ≤ batas ini untuk finalisasi bobot</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <div class="font-semibold">Tentang Consistency Ratio</div>
                            <div class="text-sm">Nilai CR ≤ 0.1 menunjukkan bahwa perbandingan berpasangan sudah konsisten. Jika CR > 0.1, perlu dilakukan revisi terhadap perbandingan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
