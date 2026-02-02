<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li>Riwayat Versi</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Riwayat Versi</h1>
                <p class="text-base-content/60">{{ $template->name }}</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.edit', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        <!-- Template Info Card -->
        <x-ui.card>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="font-semibold text-lg">{{ $template->name }}</h2>
                    <p class="text-base-content/70">{{ $template->description ?? 'Tidak ada deskripsi' }}</p>
                </div>
                <div class="flex gap-2">
                    <x-ui.button type="outline" href="{{ route('admin.kpi-forms.edit', $template) }}">
                        Edit Info
                    </x-ui.button>
                    <x-ui.button type="primary" href="{{ route('admin.kpi-forms.builder', $template) }}">
                        Edit Struktur
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <!-- Versions List -->
        <x-ui.card title="Daftar Versi">
            @if(session('success'))
            <x-ui.alert type="success" class="mb-4">{{ session('success') }}</x-ui.alert>
            @endif

            @if(session('error'))
            <x-ui.alert type="error" class="mb-4">{{ session('error') }}</x-ui.alert>
            @endif

            <div class="space-y-4">
                @forelse($versions ?? [] as $version)
                <div
                    class="border rounded-lg p-4 {{ $loop->first ? 'border-primary bg-primary/5' : 'border-base-300' }}">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h3 class="font-semibold text-lg">
                                    Versi {{ $version->version }}
                                </h3>
                                @if($loop->first)
                                <x-ui.badge type="primary">Terbaru</x-ui.badge>
                                @endif
                                @php
                                $statusBadge = match($version->status ?? 'draft') {
                                'draft' => 'warning',
                                'published' => 'success',
                                'archived' => 'neutral',
                                default => 'ghost'
                                };
                                @endphp
                                <x-ui.badge :type="$statusBadge">
                                    {{ ucfirst($version->status ?? 'Draft') }}
                                </x-ui.badge>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-base-content/70">
                                <div>
                                    <span class="font-medium">Dibuat:</span>
                                    <div>{{ $version->created_at->format('d M Y H:i') }}</div>
                                </div>
                                <div>
                                    <span class="font-medium">Jumlah Seksi:</span>
                                    <div>{{ $version->sections->count() }} bagian</div>
                                </div>
                                <div>
                                    <span class="font-medium">Total Item:</span>
                                    <div>{{ $version->sections->sum(fn($s) => $s->items->count()) }} item</div>
                                </div>
                            </div>

                            @if($version->sections->count() > 0)
                            <div class="mt-3">
                                <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                                    <input type="checkbox" class="peer">
                                    <div class="collapse-title text-sm font-medium">
                                        Lihat Struktur Form
                                    </div>
                                    <div class="collapse-content">
                                        <div class="space-y-3 pt-2">
                                            @foreach($version->sections as $section)
                                            <div class="bg-base-100 p-3 rounded-lg border border-base-300">
                                                <h4 class="font-medium">{{ $section->title }}</h4>
                                                @if($section->description)
                                                <p class="text-sm text-base-content/70 mt-1">{{ $section->description }}
                                                </p>
                                                @endif
                                                @if($section->items->count() > 0)
                                                <div class="mt-2">
                                                    <div class="text-xs text-base-content/60 mb-1">Items
                                                        ({{ $section->items->count() }}):</div>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($section->items->take(3) as $item)
                                                        <x-ui.badge type="ghost" size="xs">{{ $item->label }}
                                                        </x-ui.badge>
                                                        @endforeach
                                                        @if($section->items->count() > 3)
                                                        <x-ui.badge type="ghost" size="xs">
                                                            +{{ $section->items->count() - 3 }} lainnya</x-ui.badge>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if($loop->first && ($version->status ?? 'draft') === 'draft')
                            <x-ui.button type="outline" size="sm"
                                href="{{ route('admin.kpi-forms.builder', $template) }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </x-ui.button>
                            @endif

                            <x-ui.button type="ghost" size="sm"
                                href="{{ route('admin.kpi-forms.preview', $template) }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview
                            </x-ui.button>

                            @if(($version->status ?? 'draft') === 'draft' && !$loop->first)
                            <x-ui.button type="ghost" size="sm" class="text-error"
                                onclick="document.getElementById('delete-version-{{ $version->id }}').showModal()">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus
                            </x-ui.button>

                            <!-- Delete Version Modal -->
                            <x-ui.modal id="delete-version-{{ $version->id }}" title="Hapus Versi">
                                <x-ui.alert type="warning" class="mb-4">
                                    <strong>Peringatan!</strong> Tindakan ini akan menghapus versi
                                    {{ $version->version }} secara permanen.
                                </x-ui.alert>
                                <p>Apakah Anda yakin ingin menghapus versi ini?</p>
                                <x-slot:actions>
                                    <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
                                    <form method="POST"
                                        action="{{ route('admin.kpi-forms.delete-version', [$template, $version]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="error" :isSubmit="true">Ya, Hapus</x-ui.button>
                                    </form>
                                </x-slot:actions>
                            </x-ui.modal>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-base-content/30" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold mb-2">Belum Ada Versi</h3>
                    <p class="text-base-content/60 mb-6">Template ini belum memiliki riwayat versi.</p>
                    <x-ui.button type="primary" href="{{ route('admin.kpi-forms.builder', $template) }}">
                        Buat Form di Builder
                    </x-ui.button>
                </div>
                @endforelse
            </div>

            @if(method_exists($versions ?? collect(), 'hasPages') && $versions->hasPages())
            <div class="mt-6">
                {{ $versions->links() }}
            </div>
            @endif
        </x-ui.card>

        <!-- Version Timeline Info -->
        <x-ui.card title="Informasi Versi">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-base-100 rounded-lg border border-base-300">
                    <div class="stat-title">Total Versi</div>
                    <div class="stat-value text-primary">{{ ($versions ?? collect())->count() }}</div>
                    <div class="stat-desc">Sejak template dibuat</div>
                </div>
                <div class="stat bg-base-100 rounded-lg border border-base-300">
                    <div class="stat-title">Versi Published</div>
                    <div class="stat-value text-success">
                        {{ ($versions ?? collect())->where('status', 'published')->count() }}</div>
                    <div class="stat-desc">Versi yang sudah dipublish</div>
                </div>
                <div class="stat bg-base-100 rounded-lg border border-base-300">
                    <div class="stat-title">Versi Draft</div>
                    <div class="stat-value text-warning">
                        {{ ($versions ?? collect())->where('status', 'draft')->count() }}</div>
                    <div class="stat-desc">Versi yang masih draft</div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
