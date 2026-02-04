<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Profil Saya</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold">Profil Saya</h1>
            <p class="text-base-content/60">Kelola informasi akun Anda</p>
        </div>
    </x-slot:header>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <x-ui.card>
                <div class="flex flex-col items-center text-center">
                    <div class="avatar placeholder mb-4">
                        <div class="bg-primary text-primary-content rounded-full w-24 flex items-center justify-center">
                            <span class="text-3xl">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold">{{ auth()->user()->name }}</h2>
                    <p class="text-base-content/70">{{ auth()->user()->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach(auth()->user()->roles as $role)
                        <x-ui.badge variant="primary">{{ $role->name }}</x-ui.badge>
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
            </x-ui.card>
        </div>

        <!-- Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Update Profile -->
            <x-ui.card title="Informasi Profil">
                <p class="text-sm text-base-content/70 mb-4">Perbarui nama dan alamat email akun Anda</p>

                <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-ui.input type="text" name="name" label="Nama" value="{{ old('name', auth()->user()->name) }}"
                        required />

                    <x-ui.input type="email" name="email" label="Email"
                        value="{{ old('email', auth()->user()->email) }}" required />

                    <div class="flex justify-end">
                        <x-ui.button type="primary" :isSubmit="true">Simpan</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <!-- Update Password -->
            <x-ui.card title="Ubah Password">
                <p class="text-sm text-base-content/70 mb-4">Pastikan menggunakan password yang kuat dan unik</p>

                <form action="{{ route('admin.profile.update-password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-ui.input type="password" name="current_password" label="Password Saat Ini" required />

                    <x-ui.input type="password" name="password" label="Password Baru" required />

                    <x-ui.input type="password" name="password_confirmation" label="Konfirmasi Password Baru"
                        required />

                    <div class="flex justify-end">
                        <x-ui.button type="primary" :isSubmit="true">Ubah Password</x-ui.button>
                    </div>
                </form>
            </x-ui.card>


        </div>
    </div>



</x-layouts.admin>
