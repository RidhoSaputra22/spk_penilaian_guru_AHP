<x-ui.card title="Backup & Restore">
    <p class="text-sm text-base-content/70 mb-4">Kelola backup database sistem</p>

    <div class="space-y-6">
        <!-- Create Backup -->
        <div class="border border-base-300 rounded-lg p-4">
            <h4 class="font-semibold mb-2">Buat Backup Baru</h4>
            <p class="text-sm text-base-content/70 mb-4">
                Buat cadangan database lengkap termasuk semua data penilaian, pengguna, dan pengaturan.
            </p>
            <form action="{{ route('admin.settings.create-backup') }}" method="POST">
                @csrf
                <x-ui.button type="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Buat Backup Sekarang
                </x-ui.button>
            </form>
        </div>

        <!-- Backup Schedule -->
        <div class="border border-base-300 rounded-lg p-4">
            <h4 class="font-semibold mb-2">Jadwal Backup Otomatis</h4>
            <p class="text-sm text-base-content/70 mb-4">
                Atur jadwal backup otomatis untuk mencegah kehilangan data.
            </p>
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" class="checkbox checkbox-primary" />
                    <span class="label-text">Aktifkan backup otomatis harian</span>
                </label>
            </div>
            <div class="text-sm text-base-content/60 mt-2">
                Backup akan dilakukan setiap hari pukul 02:00 WIB
            </div>
        </div>

        <!-- Recent Backups -->
        <div>
            <h4 class="font-semibold mb-3">Backup Terbaru</h4>
            <div class="space-y-2">
                <div class="alert">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <div class="font-semibold">Backup_2026_02_05_02_00.sql</div>
                        <div class="text-sm text-base-content/70">5 Februari 2026, 02:00 WIB • 12.5 MB</div>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn btn-sm btn-ghost">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </button>
                        <button class="btn btn-sm btn-ghost text-error">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning -->
        <div class="alert alert-warning">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <div class="font-semibold">Penting!</div>
                <div class="text-sm">Selalu simpan backup di lokasi yang aman dan terpisah dari server aplikasi. Restore backup akan menghapus semua data saat ini.</div>
            </div>
        </div>
    </div>
</x-ui.card>
