<x-ui.card title="Pengaturan Grade">
    <p class="text-sm text-base-content/70 mb-4">Konfigurasi rentang nilai untuk setiap grade</p>

    <div class="alert alert-info mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <div class="text-sm">Pengaturan skala penilaian dikelola melalui menu <strong>Scoring Scales</strong>. Halaman ini menampilkan informasi skala yang tersedia.</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Skala</th>
                    <th>Tipe</th>
                    <th>Range</th>
                    <th>Jumlah Opsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scoringScales as $scale)
                    <tr>
                        <td class="font-semibold">{{ $scale->name }}</td>
                        <td>
                            <x-ui.badge variant="{{ $scale->scale_type === 'numeric' ? 'info' : 'success' }}">
                                {{ ucfirst($scale->scale_type) }}
                            </x-ui.badge>
                        </td>
                        <td class="text-sm text-base-content/70">
                            @if($scale->scale_type === 'numeric')
                                {{ $scale->min_value ?? 0 }} - {{ $scale->max_value ?? 100 }}
                                @if($scale->step) (step: {{ $scale->step }}) @endif
                            @else
                                Categorical
                            @endif
                        </td>
                        <td>
                            <x-ui.badge variant="info">{{ $scale->options_count ?? 0 }} opsi</x-ui.badge>
                        </td>
                        <td>
                            <a href="{{ route('admin.scoring-scales.show', $scale) }}" class="btn btn-sm btn-ghost">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-base-content/70">
                            Belum ada skala penilaian. Buat skala baru di menu <a href="{{ route('admin.scoring-scales.index') }}" class="link link-primary">Scoring Scales</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($scoringScales->isNotEmpty())
        <div class="flex justify-end mt-4">
            <a href="{{ route('admin.scoring-scales.index') }}" class="btn btn-primary">
                Kelola Skala Penilaian
            </a>
        </div>
    @endif
</x-ui.card>
