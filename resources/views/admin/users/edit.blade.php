<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
        <li><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
        <li>Edit User</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit User - {{ $user->name }}</h1>
                <p class="text-base-content/60">Perbarui informasi akun pengguna</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.users.show', $user) }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form -->
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
                                placeholder="Masukkan nama lengkap"
                                value="{{ old('name', $user->name) }}"
                                error="{{ $errors->first('name') }}"
                                required
                            />
                            <x-ui.input
                                name="email"
                                label="Email"
                                type="email"
                                placeholder="email@example.com"
                                value="{{ old('email', $user->email) }}"
                                error="{{ $errors->first('email') }}"
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
                                selected="{{ old('role', $user->roles->first()->key ?? '') }}"
                                required
                            />
                            <x-ui.select
                                name="status"
                                label="Status"
                                :options="[
                                    'active' => 'Aktif',
                                    'inactive' => 'Nonaktif'
                                ]"
                                selected="{{ old('status', $user->status) }}"
                                :searchable="false"
                            />
                        </div>
                    </div>

                    <!-- Password (Optional for edit) -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Password</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input
                                name="password"
                                label="Password Baru"
                                type="password"
                                placeholder="Kosongkan jika tidak ingin mengubah"
                                error="{{ $errors->first('password') }}"
                            />
                            <x-ui.input
                                name="password_confirmation"
                                label="Konfirmasi Password"
                                type="password"
                                placeholder="Ulangi password baru"
                                error="{{ $errors->first('password_confirmation') }}"
                            />
                        </div>
                        <p class="text-sm text-base-content/60 mt-2">Kosongkan kedua field password jika tidak ingin mengubah password.</p>
                    </div>

                    <!-- Additional Info (Dynamic based on role) -->
                    @php
                        $currentRole = old('role', $user->roles->first()->key ?? 'admin');
                    @endphp
                    <div x-data="{ role: '{{ $currentRole }}' }" x-init="
                        // Watch for role select changes
                        $nextTick(() => {
                            const roleSelect = document.querySelector('[name=role]');
                            if (roleSelect) {
                                roleSelect.addEventListener('change', () => {
                                    role = roleSelect.value;
                                });
                            }
                        })
                    ">
                        <!-- Teacher Profile -->
                        <div x-show="role === 'teacher'" x-cloak class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Profil Guru</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.input
                                    name="employee_no"
                                    label="Nomor Pegawai"
                                    placeholder="Nomor Induk/NUPTK"
                                    value="{{ old('employee_no', $user->teacherProfile->employee_no ?? '') }}"
                                />
                                <x-ui.input
                                    name="subject"
                                    label="Mata Pelajaran"
                                    placeholder="Mata pelajaran utama"
                                    value="{{ old('subject', $user->teacherProfile->subject ?? '') }}"
                                />
                                <x-ui.input
                                    name="employment_status"
                                    label="Status Kepegawaian"
                                    placeholder="PNS/Honor/Kontrak"
                                    value="{{ old('employment_status', $user->teacherProfile->employment_status ?? '') }}"
                                />
                                <x-ui.input
                                    name="position"
                                    label="Jabatan"
                                    placeholder="Jabatan/Posisi"
                                    value="{{ old('position', $user->teacherProfile->position ?? '') }}"
                                />
                                @if($teacherGroups->count() > 0)
                                <x-ui.select
                                    name="teacher_group_id"
                                    label="Kelompok Guru"
                                    :options="collect($teacherGroups)->pluck('name', 'id')->toArray()"
                                    selected="{{ old('teacher_group_id', $user->teacherProfile?->groups?->first()?->id ?? '') }}"
                                    placeholder="Pilih kelompok guru"
                                />
                                @else
                                <div class="space-y-2">
                                    <label class="label">
                                        <span class="label-text font-semibold">Kelompok Guru</span>
                                    </label>
                                    <div class="text-sm text-base-content/60">
                                        Belum ada kelompok guru yang tersedia.
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Assessor Profile -->
                        <div x-show="role === 'assessor'" x-cloak class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Profil Penilai</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.select
                                    name="assessor_type"
                                    label="Tipe Penilai"
                                    :options="[
                                        'principal' => 'Kepala Sekolah',
                                        'supervisor' => 'Pengawas',
                                        'peer' => 'Rekan Sejawat'
                                    ]"
                                    selected="{{ old('assessor_type', $user->assessorProfile->meta['type'] ?? 'peer') }}"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.users.show', $user) }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update User
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-ui.card title="Informasi Edit">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">User Saat Ini</p>
                        <p class="text-sm">{{ $user->name }}</p>
                        <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                        <p class="text-sm">
                            <span class="badge badge-{{ $user->status === 'active' ? 'success' : 'error' }} badge-sm">
                                {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </p>
                    </div>

                    <div class="divider"></div>

                    <div class="space-y-3">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-warning/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Perubahan Role</p>
                                <p class="text-xs text-base-content/60">Mengubah role akan mempengaruhi akses dan profil pengguna.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-info/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Password</p>
                                <p class="text-xs text-base-content/60">Kosongkan field password jika tidak ingin mengubah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
