<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.scoring-scales.index') }}">Skala Penilaian</a></li>
        <li>{{ $scoringScale->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $scoringScale->name }}</h1>
                <p class="text-base-content/60">Detail skala penilaian</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="primary" href="{{ route('admin.scoring-scales.edit', $scoringScale) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Skala
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <x-ui.card title="Informasi Skala">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Nama Skala</span>
                        </label>
                        <p class="text-lg">{{ $scoringScale->name }}</p>
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Tipe Skala</span>
                        </label>
                        <x-ui.badge type="{{ $scoringScale->scale_type === 'numeric' ? 'info' : 'success' }}">
                            {{ $scoringScale->scale_type === 'numeric' ? 'Numerik' : 'Teks' }}
                        </x-ui.badge>
                    </div>

                    @if($scoringScale->description)
                    <div class="md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Deskripsi</span>
                        </label>
                        <p class="text-base-content/80">{{ $scoringScale->description }}</p>
                    </div>
                    @endif

                    @if($scoringScale->scale_type === 'numeric')
                    <div class="md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Range Nilai</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="stats stats-horizontal shadow-sm">
                                <div class="stat px-4 py-2">
                                    <div class="stat-title text-xs">Min</div>
                                    <div class="stat-value text-lg">{{ $scoringScale->min_value }}</div>
                                </div>
                                <div class="stat px-4 py-2">
                                    <div class="stat-title text-xs">Max</div>
                                    <div class="stat-value text-lg">{{ $scoringScale->max_value }}</div>
                                </div>
                                @if($scoringScale->step)
                                <div class="stat px-4 py-2">
                                    <div class="stat-title text-xs">Step</div>
                                    <div class="stat-value text-lg">{{ $scoringScale->step }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Options -->
            <x-ui.card title="Opsi Skala Penilaian ({{ $scoringScale->options->count() }} Opsi)">
                <div class="space-y-4">
                    @foreach($scoringScale->options->sortBy('sort_order') as $option)
                    <div class="flex justify-between items-center p-4 bg-base-100 rounded-lg border">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <x-ui.badge type="primary" size="lg">
                                    {{ $option->value }}
                                </x-ui.badge>
                                <div>
                                    <div class="font-semibold">{{ $option->label }}</div>
                                    @if($option->description)
                                        <div class="text-sm text-base-content/70">{{ $option->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-base-content/50">
                            Urutan: {{ $option->sort_order }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Stats -->
            <x-ui.card title="Statistik">
                <div class="stats stats-vertical w-full">
                    <div class="stat">
                        <div class="stat-title">Total Opsi</div>
                        <div class="stat-value text-primary">{{ $scoringScale->options->count() }}</div>
                    </div>

                    @if($scoringScale->scale_type === 'numeric')
                    <div class="stat">
                        <div class="stat-title">Range</div>
                        <div class="stat-value text-sm">
                            {{ $scoringScale->min_value }} - {{ $scoringScale->max_value }}
                        </div>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Metadata -->
            <x-ui.card title="Informasi Tambahan">
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Dibuat</span>
                        <span class="text-sm text-base-content/70">
                            {{ $scoringScale->created_at->format('d M Y') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Waktu Buat</span>
                        <span class="text-sm text-base-content/70">
                            {{ $scoringScale->created_at->format('H:i') }}
                        </span>
                    </div>

                    @if($scoringScale->updated_at != $scoringScale->created_at)
                    <div class="divider my-2"></div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Terakhir Update</span>
                        <span class="text-sm text-base-content/70">
                            {{ $scoringScale->updated_at->format('d M Y H:i') }}
                        </span>
                    </div>
                    @endif
                </div>
            </x-ui.card>

            <!-- Actions -->
            <x-ui.card title="Aksi">
                <div class="space-y-2">
                    <x-ui.button type="primary" class="w-full"
                                href="{{ route('admin.scoring-scales.edit', $scoringScale) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Skala
                    </x-ui.button>

                    <form method="POST" action="{{ route('admin.scoring-scales.destroy', $scoringScale) }}"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus skala ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="error" class="w-full" :isSubmit="true">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Skala
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
