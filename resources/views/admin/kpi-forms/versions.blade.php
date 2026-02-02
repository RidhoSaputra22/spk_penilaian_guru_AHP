@extends('layouts.admin')

@section('title', 'Riwayat Versi - ' . $template->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.kpi-forms.index') }}" class="btn btn-ghost btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <div>
            <h1 class="text-2xl font-bold">Riwayat Versi</h1>
            <p class="text-base-content/70">{{ $template->name }}</p>
        </div>
    </div>

    <!-- Template Info Card -->
    <x-ui.card>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-lg">{{ $template->name }}</h2>
                <p class="text-base-content/70">{{ $template->description ?? 'Tidak ada deskripsi' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.kpi-forms.edit', $template) }}" class="btn btn-outline btn-sm">
                    Edit Info
                </a>
                <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-primary btn-sm">
                    Edit Struktur
                </a>
            </div>
        </div>
    </x-ui.card>

    <!-- Versions List -->
    <x-ui.card title="Versi Template">
        <div class="space-y-4">
            @forelse($versions as $version)
                <div class="border border-base-300 rounded-lg p-4 {{ $loop->first ? 'border-primary bg-primary/5' : '' }}">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold">
                                    Versi {{ $version->version }}
                                    @if($loop->first)
                                        <x-ui.badge variant="primary">Terbaru</x-ui.badge>
                                    @endif
                                </h3>
                                <x-ui.badge variant="{{ $version->status === 'published' ? 'success' : ($version->status === 'draft' ? 'warning' : 'info') }}">
                                    {{ ucfirst($version->status) }}
                                </x-ui.badge>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-base-content/70">
                                <div>
                                    <span class="font-medium">Dibuat:</span>
                                    <div>{{ $version->created_at->format('d M Y H:i') }}</div>
                                </div>
                                <div>
                                    <span class="font-medium">Sections:</span>
                                    <div>{{ $version->sections->count() }} bagian</div>
                                </div>
                                <div>
                                    <span class="font-medium">Total Items:</span>
                                    <div>{{ $version->sections->sum(fn($s) => $s->items->count()) }} item</div>
                                </div>
                            </div>

                            @if($version->sections->count() > 0)
                                <div class="mt-3">
                                    <details class="collapse collapse-arrow bg-base-200">
                                        <summary class="collapse-title text-sm font-medium">
                                            Lihat Struktur Form
                                        </summary>
                                        <div class="collapse-content">
                                            <div class="space-y-3">
                                                @foreach($version->sections as $section)
                                                    <div class="bg-base-100 p-3 rounded">
                                                        <h4 class="font-medium">{{ $section->title }}</h4>
                                                        @if($section->description)
                                                            <p class="text-sm text-base-content/70 mt-1">{{ $section->description }}</p>
                                                        @endif
                                                        @if($section->items->count() > 0)
                                                            <div class="mt-2">
                                                                <div class="text-xs text-base-content/60 mb-1">Items ({{ $section->items->count() }}):</div>
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach($section->items->take(3) as $item)
                                                                        <span class="badge badge-outline badge-xs">{{ $item->label }}</span>
                                                                    @endforeach
                                                                    @if($section->items->count() > 3)
                                                                        <span class="badge badge-outline badge-xs">+{{ $section->items->count() - 3 }} lainnya</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            @if($loop->first)
                                <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-outline btn-sm">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                            @endif

                            <a href="{{ route('admin.kpi-forms.preview', $template) }}" class="btn btn-ghost btn-sm">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview
                            </a>

                            @if($version->status === 'draft' && !$loop->first)
                                <button onclick="deleteVersionModal_{{ $version->id }}.showModal()" class="btn btn-ghost btn-sm text-error">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Delete Version Modal -->
                @if($version->status === 'draft' && !$loop->first)
                    <x-ui.modal id="deleteVersionModal_{{ $version->id }}" title="Hapus Versi">
                        <form method="POST" action="#" class="space-y-4">
                            @csrf
                            @method('DELETE')
                            <x-ui.alert type="warning">
                                <strong>Peringatan!</strong> Tindakan ini akan menghapus versi {{ $version->version }} secara permanen.
                            </x-ui.alert>
                            <p>Apakah Anda yakin ingin menghapus versi ini?</p>

                            <x-slot name="actions">
                                <x-ui.button variant="outline" type="button" onclick="deleteVersionModal_{{ $version->id }}.close()">
                                    Batal
                                </x-ui.button>
                                <x-ui.button variant="error" type="submit">
                                    Hapus Versi
                                </x-ui.button>
                            </x-slot>
                        </form>
                    </x-ui.modal>
                @endif

            @empty
                <div class="text-center py-8">
                    <div class="flex flex-col items-center gap-3">
                        <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-center">
                            <p class="font-semibold">Belum ada versi</p>
                            <p class="text-sm text-base-content/70">Template ini belum memiliki versi apapun</p>
                        </div>
                        <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-primary btn-sm">
                            Buat Versi Pertama
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        @if($versions->count() > 0)
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.kpi-forms.new-version', $template) }}">
                    @csrf
                    <x-ui.button variant="outline" type="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Versi Baru
                    </x-ui.button>
                </form>
            </x-slot>
        @endif
    </x-ui.card>
</div>
@endsection
