<x-ui.card title="Pengaturan AHP">
    <p class="text-sm text-base-content/70 mb-4">Konfigurasi metode Analytic Hierarchy Process</p>

    <form action="{{ route('admin.settings.update-ahp') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="label">
                <span class="label-text font-semibold">Batas Consistency Ratio (CR)</span>
            </label>
            <div class="flex items-center gap-4">
                <input type="number" step="0.01" name="cr_threshold"
                    value="{{ old('cr_threshold', $institution->meta['ahp_cr_threshold'] ?? 0.10) }}"
                    class="input input-bordered w-32" required />
                <span class="text-sm text-base-content/70">Nilai CR harus ≤ batas ini untuk finalisasi bobot</span>
            </div>
        </div>

        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <div class="font-semibold">Tentang Consistency Ratio</div>
                <div class="text-sm">Nilai CR ≤ 0.1 menunjukkan bahwa perbandingan berpasangan sudah
                    konsisten. Jika CR > 0.1, perlu dilakukan revisi terhadap perbandingan.</div>
            </div>
        </div>

        <div class="flex justify-end">
            <x-ui.button type="primary" :isSubmit="true">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>
