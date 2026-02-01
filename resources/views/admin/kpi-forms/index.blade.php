<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Template Form KPI</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Template Form KPI</h1>
                <p class="text-base-content/60">Kelola template form penilaian KPI</p>
            </div>
            <x-ui.button type="primary" href="{{ route('admin.kpi-forms.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Buat Template Baru
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates ?? [] as $template)
            <x-ui.card class="relative">
                <!-- Status Badge -->
                <div class="absolute top-4 right-4">
                    @php
                        $latestVersion = $template->versions->first();
                        $statusBadge = match($latestVersion?->status ?? 'draft') {
                            'draft' => 'ghost',
                            'published' => 'success',
                            'archived' => 'neutral',
                            default => 'ghost'
                        };
                    @endphp
                    <x-ui.badge :type="$statusBadge" size="sm">
                        {{ ucfirst($latestVersion?->status ?? 'draft') }}
                    </x-ui.badge>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-bold pr-20">{{ $template->name }}</h3>
                    <p class="text-sm text-base-content/60 mt-1">{{ Str::limit($template->description, 100) }}</p>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <x-ui.badge type="info" size="sm">
                        {{ $template->versions->count() }} versi
                    </x-ui.badge>
                    @if($latestVersion)
                        <x-ui.badge type="ghost" size="sm">
                            {{ $latestVersion->sections->count() ?? 0 }} seksi
                        </x-ui.badge>
                        <x-ui.badge type="ghost" size="sm">
                            {{ $latestVersion->sections->sum(fn($s) => $s->items->count()) ?? 0 }} item
                        </x-ui.badge>
                    @endif
                </div>

                <div class="text-sm text-base-content/60 mb-4">
                    <div class="flex justify-between">
                        <span>Dibuat</span>
                        <span>{{ $template->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Oleh</span>
                        <span>{{ $template->creator?->name ?? 'System' }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="primary" size="sm" href="{{ route('admin.kpi-forms.builder', $template) }}" class="flex-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Builder
                    </x-ui.button>
                    <x-ui.button type="ghost" size="sm" href="{{ route('admin.kpi-forms.preview', $template) }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </x-ui.button>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/>
                            </svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                            <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">Edit Info</a></li>
                            <li><a href="{{ route('admin.kpi-forms.clone', $template) }}">Duplikat</a></li>
                            <li><a href="{{ route('admin.kpi-forms.versions', $template) }}">Riwayat Versi</a></li>
                            <li><a class="text-error" onclick="document.getElementById('delete-{{ $template->id }}').showModal()">Hapus</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Delete Modal -->
                <x-ui.modal id="delete-{{ $template->id }}" title="Hapus Template">
                    <p>Anda yakin ingin menghapus template <strong>{{ $template->name }}</strong>?</p>
                    <p class="text-sm text-error mt-2">Semua versi dan data terkait akan dihapus!</p>
                    <x-slot:actions>
                        <form method="dialog">
                            <button class="btn btn-ghost">Batal</button>
                        </form>
                        <form method="POST" action="{{ route('admin.kpi-forms.destroy', $template) }}">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="error">Hapus</x-ui.button>
                        </form>
                    </x-slot:actions>
                </x-ui.modal>
            </x-ui.card>
        @empty
            <div class="col-span-full">
                <x-ui.card>
                    <div class="text-center py-12 text-base-content/60">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-lg">Belum ada template form KPI</p>
                        <p class="text-sm mb-4">Buat template pertama untuk memulai</p>
                        <x-ui.button type="primary" href="{{ route('admin.kpi-forms.create') }}">
                            Buat Template Pertama
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        @endforelse
    </div>

    @if(isset($templates) && $templates->hasPages())
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @endif
</x-layouts.admin>
