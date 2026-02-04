<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li>Form Builder</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Simple Form Builder</h1>
                <p class="text-base-content/60">
                    {{ $template->name }} •
                    Versi {{ $version->version ?? '1.0' }} •
                    <x-ui.badge type="{{ ($version->status ?? 'draft') === 'published' ? 'success' : 'ghost' }}"
                        size="xs">
                        {{ ucfirst($version->status ?? 'Draft') }}
                    </x-ui.badge>
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.preview', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Preview
                </x-ui.button>
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

    @php
    $criteriaOptions = collect($criteriaNodes ?? [])->mapWithKeys(fn($n) => [$n->id => $n->name])->toArray();
    $fieldTypes = [
    'numeric' => 'Skor Numerik',
    'dropdown' => 'Dropdown Skala',
    'radio' => 'Radio Button',
    'yesno' => 'Ya/Tidak',
    'textarea' => 'Catatan'
    ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Sections List -->
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Daftar Seksi</h3>
                    @if(($version->status ?? 'draft') !== 'published')
                    <x-ui.button type="primary" size="sm"
                        href="{{ route('admin.kpi-forms.create-section', $version) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Seksi
                    </x-ui.button>
                    @else
                    <x-ui.badge type="warning" size="sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Form Published (Read-only)
                    </x-ui.badge>
                    @endif
                </div>

                @forelse($version->sections ?? [] as $section)
                <div class="border border-base-300 rounded-lg mb-4" x-data="{ expanded: true }">
                    <!-- Section Header -->
                    <div class="flex items-center justify-between p-4 bg-base-200 rounded-t-lg cursor-pointer"
                        @click="expanded = !expanded">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            <div>
                                <h4 class="font-semibold">{{ $section->title }}</h4>
                                @if($section->description)
                                <p class="text-sm text-base-content/60">{{ $section->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2" @click.stop>
                            <x-ui.badge type="ghost" size="sm">{{ $section->items->count() ?? 0 }} item</x-ui.badge>
                            @if(($version->status ?? 'draft') !== 'published')
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </label>
                                <ul tabindex="0"
                                    class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 z-50">
                                    <li>
                                        <a href="{{ route('admin.kpi-forms.create-item', $section) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Tambah Item
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.kpi-forms.edit-section', $section) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Seksi
                                        </a>
                                    </li>
                                    <li class="text-error">
                                        <form method="POST"
                                            action="{{ route('admin.kpi-forms.delete-section', $section) }}"
                                            onsubmit="return confirm('Yakin hapus seksi ini? Semua item di dalamnya akan ikut terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex items-center w-full">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus Seksi
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Section Items -->
                    <div x-show="expanded" x-collapse class="p-4 space-y-3">
                        @forelse($section->items ?? [] as $item)
                        <div class="flex items-start gap-3 p-3 bg-base-100 border border-base-200 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $item->label }}</span>
                                    @if($item->is_required)
                                    <span class="text-error">*</span>
                                    @endif
                                </div>
                                @if($item->help_text)
                                <p class="text-sm text-base-content/60 mt-1">{{ $item->help_text }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-2">
                                    <x-ui.badge type="info" size="xs">
                                        {{ $fieldTypes[$item->field_type] ?? $item->field_type }}
                                    </x-ui.badge>
                                    @if($item->min_value !== null || $item->max_value !== null)
                                    <x-ui.badge type="ghost" size="xs">{{ $item->min_value ?? '?' }} -
                                        {{ $item->max_value ?? '?' }}
                                    </x-ui.badge>
                                    @endif
                                </div>
                            </div>
                            @if(($version->status ?? 'draft') !== 'published')
                            <div class="flex gap-1">
                                <a href="{{ route('admin.kpi-forms.edit-item', $item) }}" class="btn btn-ghost btn-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.kpi-forms.delete-item', $item) }}"
                                    onsubmit="return confirm('Yakin hapus item ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs text-error">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div
                            class="text-center py-6 text-base-content/60 border-2 border-dashed border-base-300 rounded-lg">
                            <p class="text-sm">Belum ada item di seksi ini</p>
                            @if(($version->status ?? 'draft') !== 'published')
                            <a href="{{ route('admin.kpi-forms.create-item', $section) }}"
                                class="btn btn-ghost btn-sm mt-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Tambah Item
                            </a>
                            @endif
                        </div>
                        @endforelse
                    </div>
                </div>
                @empty
                <div class="text-center py-12 text-base-content/60 border-2 border-dashed border-base-300 rounded-lg">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg mb-2">Form belum memiliki seksi</p>
                    <p class="text-sm mb-4">Mulai dengan menambahkan seksi pertama</p>
                    <x-ui.button type="primary" href="{{ route('admin.kpi-forms.create-section', $version) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah Seksi Pertama
                    </x-ui.button>
                </div>
                @endforelse
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Template Info -->
            <x-ui.card title="Informasi Template">
                <div class="space-y-3">
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Status</p>
                        @php
                        $statusBadge = match($version->status ?? 'draft') {
                        'draft' => 'ghost',
                        'published' => 'success',
                        'archived' => 'neutral',
                        default => 'ghost'
                        };
                        @endphp
                        <x-ui.badge :type="$statusBadge">
                            {{ ucfirst($version->status ?? 'Draft') }}
                        </x-ui.badge>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Versi</p>
                        <p class="text-sm">v{{ $version->version ?? '1.0' }}</p>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Total Seksi</p>
                        <p class="text-sm">{{ ($version->sections ?? collect())->count() }} seksi</p>
                    </div>

                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Total Item</p>
                        <p class="text-sm">
                            {{ ($version->sections ?? collect())->sum(fn($s) => $s->items->count() ?? 0) }} item</p>
                    </div>
                </div>
            </x-ui.card>

            <!-- Quick Tips -->
            <x-ui.card title="Tips">
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Buat seksi untuk mengelompokkan item yang relevan</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pilih tipe field sesuai dengan jenis input yang diperlukan</span>
                    </div>

                </div>
            </x-ui.card>

            <!-- Actions -->
            @if(($version->status ?? 'draft') === 'draft')
            <x-ui.card title="Aksi">
                <div class="space-y-3">
                    <x-ui.button type="success" class="w-full"
                        onclick="document.getElementById('publish-modal').showModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Publish Form
                    </x-ui.button>
                    <x-ui.button type="outline" class="w-full" href="{{ route('admin.kpi-forms.preview', $template) }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview Form
                    </x-ui.button>
                </div>
            </x-ui.card>
            @endif
        </div>
    </div>

    <!-- Publish Modal -->
    <x-ui.modal id="publish-modal" title="Publish Form">
        <p>Anda yakin ingin mempublish form <strong>{{ $template->name }}</strong> versi
            {{ $version->version ?? '1.0' }}?</p>
        <x-ui.alert type="warning" class="mt-4">

            <span>Form yang sudah dipublish tidak dapat diubah lagi. Pastikan semua seksi dan item sudah benar.</span>
        </x-ui.alert>
        <div>
            <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
            <form method="POST" action="{{ route('admin.kpi-forms.publish-version', $version) }}">
                @csrf
                @method('PATCH')
                <x-ui.button type="success" :isSubmit="true">Ya, Publish</x-ui.button>
            </form>
        </div>
    </x-ui.modal>



</x-layouts.admin>
