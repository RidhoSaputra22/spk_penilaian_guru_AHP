<x-layouts.auth>
    <x-slot:title>Login</x-slot:title>

    <x-ui.card class="w-full">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto rounded-xl bg-primary flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-content" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">SPK Penilaian Guru</h1>
            <p class="text-base-content/60">Sistem Pendukung Keputusan dengan AHP</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </x-ui.alert>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <x-ui.input name="email" label="Email" type="email" placeholder="admin@example.com" required />

            <x-ui.input name="password" label="Password" type="password" placeholder="••••••••" required />

            <div class="flex items-center">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                    <span class="text-sm">Ingat saya</span>
                </label>
            </div>

            <x-ui.button type="primary" class="w-full">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Masuk
            </x-ui.button>
        </form>

        <!-- Divider -->
        <div class="my-6"></div>


    </x-ui.card>

    <!-- Footer -->
    <p class="text-center text-sm text-base-content/50 mt-4">
        © {{ date('Y') }} SPK Penilaian Guru AHP
    </p>
</x-layouts.auth>