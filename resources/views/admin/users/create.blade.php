<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.users.index') }}">Manajemen User</a></li>
        <li>Tambah User</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tambah User Baru</h1>
                <p class="text-base-content/60">Buat akun baru untuk Admin, Penilai, atau Guru</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.users.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    <!-- Basic Info -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input name="name" label="Nama Lengkap" placeholder="Masukkan nama lengkap"
                                value="{{ old('name') }}" error="{{ $errors->first('name') }}" required />
                            <x-ui.input name="email" label="Email" type="email" placeholder="email@example.com"
                                value="{{ old('email') }}" error="{{ $errors->first('email') }}" required />
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Role & Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select name="role" label="Role" :options="[
                                    'admin' => 'Admin',
                                    'assessor' => 'Penilai (Assessor)',
                                    'teacher' => 'Guru (Teacher)'
                                ]" selected="{{ old('role', $request->role) }}" required />
                            <x-ui.select name="status" label="Status" :options="[
                                    'active' => 'Aktif',
                                    'inactive' => 'Nonaktif'
                                ]" selected="{{ old('status', 'active') }}" :searchable="false" />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Password</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.input name="password" label="Password" type="password" placeholder="Min. 8 karakter"
                                error="{{ $errors->first('password') }}" required />
                            <x-ui.input name="password_confirmation" label="Konfirmasi Password" type="password"
                                placeholder="Ulangi password" error="{{ $errors->first('password_confirmation') }}"
                                required />
                        </div>
                        <p class="text-sm text-base-content/60 mt-2">Password minimal 8 karakter, kombinasi huruf dan
                            angka.</p>
                    </div>

                    <!-- Additional Info (Dynamic based on role) -->
                    <div x-data="{ role: '{{ old('role', 'admin') }}' }" x-init="
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
                                <x-ui.input name="nip" label="NIP" placeholder="Nomor Induk Pegawai"
                                    value="{{ old('nip') }}" />
                                <x-ui.input name="nuptk" label="NUPTK" placeholder="Nomor Unik Pendidik"
                                    value="{{ old('nuptk') }}" />
                                @if(($teacherGroups ?? [])->count() > 0)
                                <x-ui.select name="teacher_group_id" label="Kelompok Guru"
                                    :options="collect($teacherGroups ?? [])->pluck('name', 'id')->toArray()"
                                    selected="{{ old('teacher_group_id') }}" placeholder="Pilih kelompok guru" />
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
                                <x-ui.input name="subject" label="Mata Pelajaran" placeholder="Mata pelajaran utama"
                                    value="{{ old('subject') }}" />
                            </div>
                        </div>

                        <!-- Assessor Profile -->
                        <div x-show="role === 'assessor'" x-cloak class="border-b border-base-200 pb-6">
                            <h3 class="text-lg font-medium mb-4">Profil Penilai</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-ui.input name="assessor_position" label="Jabatan" placeholder="Jabatan penilai"
                                    value="{{ old('assessor_position') }}" />
                                <x-ui.input name="assessor_nip" label="NIP" placeholder="Nomor Induk Pegawai"
                                    value="{{ old('assessor_nip') }}" />
                            </div>
                        </div>

                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.users.index') }}">
                            Batal
                        </x-ui.button>
                        <x-ui.button type="primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan User
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-ui.card title="Panduan">
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-error/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-error font-bold text-sm">A</span>
                        </div>
                        <div>
                            <p class="font-medium">Admin</p>
                            <p class="text-sm text-base-content/60">Akses penuh ke semua fitur sistem termasuk manajemen
                                user, periode, dan AHP.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-warning/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-warning font-bold text-sm">P</span>
                        </div>
                        <div>
                            <p class="font-medium">Penilai</p>
                            <p class="text-sm text-base-content/60">Dapat menilai guru yang ditugaskan dan melihat hasil
                                penilaian.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-info/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-info font-bold text-sm">G</span>
                        </div>
                        <div>
                            <p class="font-medium">Guru</p>
                            <p class="text-sm text-base-content/60">Hanya dapat melihat hasil penilaian diri sendiri.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <x-ui.alert type="info">
                    <p class="text-sm">Link aktivasi akan dikirim ke email user setelah akun dibuat.</p>
                </x-ui.alert>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
