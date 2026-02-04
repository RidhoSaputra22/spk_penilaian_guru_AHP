<x-ui.card title="Pengaturan Email">
    <p class="text-sm text-base-content/70 mb-4">Konfigurasi SMTP untuk pengiriman email</p>

    <form action="{{ route('admin.settings.update-email') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        @php
            $emailSettings = $institution->meta['email_settings'] ?? [];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-ui.input type="text" name="smtp_host" label="SMTP Host"
                    value="{{ old('smtp_host', $emailSettings['smtp_host'] ?? '') }}"
                    placeholder="smtp.gmail.com" required />
            </div>

            <div>
                <x-ui.input type="number" name="smtp_port" label="SMTP Port"
                    value="{{ old('smtp_port', $emailSettings['smtp_port'] ?? 587) }}"
                    placeholder="587" required />
            </div>

            <div>
                <x-ui.input type="text" name="smtp_username" label="Username"
                    value="{{ old('smtp_username', $emailSettings['smtp_username'] ?? '') }}" required />
            </div>

            <div>
                <x-ui.input type="password" name="smtp_password" label="Password"
                    placeholder="Kosongkan jika tidak ingin mengubah" />
            </div>

            <div>
                <x-ui.select name="smtp_encryption" label="Encryption"
                    :options="['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None']"
                    selected="{{ old('smtp_encryption', $emailSettings['smtp_encryption'] ?? 'tls') }}" />
            </div>

            <div>
                <x-ui.input type="email" name="from_address" label="From Address"
                    value="{{ old('from_address', $emailSettings['from_address'] ?? '') }}" required />
            </div>

            <div class="md:col-span-2">
                <x-ui.input type="text" name="from_name" label="From Name"
                    value="{{ old('from_name', $emailSettings['from_name'] ?? $institution->name ?? '') }}" required />
            </div>
        </div>

        <div class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="text-sm">Pastikan kredensial SMTP Anda benar. Email akan digunakan untuk notifikasi sistem.</span>
        </div>

        <div class="flex justify-end">
            <x-ui.button type="primary" :isSubmit="true">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>
