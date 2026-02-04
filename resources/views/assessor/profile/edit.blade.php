<x-layouts.assessor>
    <x-slot:title>Profil Saya</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Profil</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <h1 class="text-2xl font-bold">Profil Saya</h1>
        <p class="text-base-content/70">Kelola informasi profil dan password akun Anda</p>
    </x-slot:header>



    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information -->
        <x-ui.card title="Informasi Profil">
            <p class="text-sm text-base-content/60 mb-4">
                Perbarui informasi profil dan email akun Anda.
            </p>

            <form action="{{ route('assessor.profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input name="name" label="Nama Lengkap" value="{{ old('name', $user->name) }}" required />

                <x-ui.input name="email" label="Email" type="email" value="{{ $user->email }}" disabled
                    helpText="Email tidak dapat diubah" />

                <x-ui.input name="title" label="Jabatan/Title" value="{{ old('title', $assessorProfile->title ?? '') }}"
                    placeholder="Contoh: Kepala Sekolah, Wakil Kepala Sekolah" />

                <div class="flex justify-end pt-4">
                    <x-ui.button type="primary" :isSubmit="true">
                        Simpan Perubahan
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Update Password -->
        <x-ui.card title="Ubah Password">
            <p class="text-sm text-base-content/60 mb-4">
                Pastikan akun Anda menggunakan password yang kuat untuk keamanan.
            </p>

            <form action="{{ route('assessor.profile.update-password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input name="current_password" label="Password Saat Ini" type="password" required />

                <x-ui.input name="password" label="Password Baru" type="password" required />

                <x-ui.input name="password_confirmation" label="Konfirmasi Password Baru" type="password" required />

                <div class="flex justify-end pt-4">
                    <x-ui.button type="primary" :isSubmit="true">
                        Ubah Password
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    <!-- Account Info -->
    <x-ui.card title="Informasi Akun" class="mt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-base-content/60">Role</p>
                <p class="font-medium">Tim Penilai (Assessor)</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Terdaftar Sejak</p>
                <p class="font-medium">{{ $user->created_at?->format('d M Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-base-content/60">Login Terakhir</p>
                <p class="font-medium">{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
        </div>
    </x-ui.card>

</x-layouts.assessor>