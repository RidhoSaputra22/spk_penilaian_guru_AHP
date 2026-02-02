<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li><a href="{{ route('admin.kpi-forms.edit', $template) }}">{{ $template->name }}</a></li>
        <li>Preview</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Preview Form</h1>
                <p class="text-base-content/60">
                    {{ $template->name }} •
                    Versi {{ $latestVersion?->version ?? '1.0' }} •
                    <x-ui.badge type="{{ ($latestVersion?->status ?? 'draft') === 'published' ? 'success' : 'ghost' }}"
                        size="xs">
                        {{ ucfirst($latestVersion?->status ?? 'Draft') }}
                    </x-ui.badge>
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.builder', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Form
                </x-ui.button>
                @if($latestVersion && ($latestVersion->sections ?? collect())->count() > 0 && $latestVersion->status ===
                'draft')
                <x-ui.button type="success" onclick="document.getElementById('publish-modal').showModal()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Publish
                </x-ui.button>
                @endif
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.index', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <div class="max-w-4xl mx-auto">
        @if($latestVersion && ($latestVersion->sections ?? collect())->count() > 0)
        <!-- Form Preview Card -->
        <x-ui.card>
            <!-- Form Header -->
            <div class="mb-6 p-4 bg-base-200 rounded-lg">
                <h2 class="text-xl font-bold mb-2">{{ $template->name }}</h2>
                @if($template->description)
                <p class="text-base-content/70 mb-3">{{ $template->description }}</p>
                @endif
                <div class="flex flex-wrap gap-2">
                    <x-ui.badge type="info">Versi {{ $latestVersion->version ?? '1.0' }}</x-ui.badge>
                    @php
                    $statusBadge = match($latestVersion->status ?? 'draft') {
                    'draft' => 'ghost',
                    'published' => 'success',
                    'archived' => 'neutral',
                    default => 'ghost'
                    };
                    @endphp
                    <x-ui.badge :type="$statusBadge">{{ ucfirst($latestVersion->status ?? 'Draft') }}</x-ui.badge>
                    <x-ui.badge type="ghost">{{ ($latestVersion->sections ?? collect())->count() }} Seksi</x-ui.badge>
                    <x-ui.badge type="ghost">
                        {{ ($latestVersion->sections ?? collect())->sum(fn($s) => $s->items->count()) }} Item
                    </x-ui.badge>
                </div>
            </div>

            <!-- Alert Info -->
            <x-ui.alert type="info" class="mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Ini adalah tampilan preview form. Field dalam mode disabled dan tidak dapat diisi.</span>
            </x-ui.alert>

            <!-- Form Sections -->
            <div class="space-y-8">
                @foreach($latestVersion->sections ?? [] as $section)
                <div class="form-section border-b border-base-200 pb-6 last:border-0">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-primary">{{ $section->title }}</h3>
                        @if($section->description)
                        <p class="text-base-content/70 mt-1">{{ $section->description }}</p>
                        @endif
                    </div>

                    <div class="space-y-6">
                        @foreach($section->items ?? [] as $item)
                        <div class="form-group">
                            <label class="label justify-start gap-1">
                                <span class="label-text font-medium">{{ $item->label }}</span>
                                @if($item->is_required)
                                <span class="text-error">*</span>
                                @endif
                            </label>

                            @if($item->help_text)
                            <p class="text-sm text-base-content/60 mb-2">{{ $item->help_text }}</p>
                            @endif

                            @switch($item->field_type)
                            @case('numeric')
                            <div class="flex items-center gap-2">
                                <input type="number" class="input input-bordered w-full max-w-xs"
                                    placeholder="Masukkan skor" min="{{ $item->min_value ?? 1 }}"
                                    max="{{ $item->max_value ?? 5 }}" disabled>
                                @if($item->min_value !== null || $item->max_value !== null)
                                <span class="text-sm text-base-content/60">
                                    ({{ $item->min_value ?? '?' }} - {{ $item->max_value ?? '?' }})
                                </span>
                                @endif
                            </div>
                            @break

                            @case('dropdown')
                            <select class="select select-bordered w-full max-w-md" disabled>
                                <option value="">-- Pilih Opsi --</option>
                                @foreach($item->options ?? [] as $option)
                                <option value="{{ $option->value }}">{{ $option->label }} ({{ $option->value }})
                                </option>
                                @endforeach
                                @if(empty($item->options) || count($item->options ?? []) === 0)
                                @for($i = ($item->min_value ?? 1); $i <= ($item->max_value ?? 5); $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                    @endif
                            </select>
                            @break

                            @case('radio')
                            <div class="space-y-2">
                                @foreach($item->options ?? [] as $option)
                                <label class="flex items-center gap-3 cursor-not-allowed">
                                    <input type="radio" name="item_{{ $item->id }}" class="radio"
                                        value="{{ $option->value }}" disabled>
                                    <span>{{ $option->label }} ({{ $option->value }})</span>
                                </label>
                                @endforeach
                                @if(empty($item->options) || count($item->options ?? []) === 0)
                                @for($i = ($item->min_value ?? 1); $i <= ($item->max_value ?? 5); $i++)
                                    <label class="flex items-center gap-3 cursor-not-allowed">
                                        <input type="radio" name="item_{{ $item->id }}" class="radio" value="{{ $i }}"
                                            disabled>
                                        <span>{{ $i }}</span>
                                    </label>
                                    @endfor
                                    @endif
                            </div>
                            @break

                            @case('yesno')
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-not-allowed">
                                    <input type="radio" name="item_{{ $item->id }}" class="radio radio-success"
                                        value="1" disabled>
                                    <span>Ya</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-not-allowed">
                                    <input type="radio" name="item_{{ $item->id }}" class="radio radio-error" value="0"
                                        disabled>
                                    <span>Tidak</span>
                                </label>
                            </div>
                            @break

                            @case('textarea')
                            <textarea class="textarea textarea-bordered w-full" rows="3"
                                placeholder="Masukkan catatan atau komentar..." disabled></textarea>
                            @break

                            @default
                            <input type="text" class="input input-bordered w-full" placeholder="Masukkan nilai"
                                disabled>
                            @endswitch

                            <div class="mt-1">
                                <x-ui.badge type="ghost" size="xs">{{ $item->field_type }}</x-ui.badge>
                                @if($item->criteriaNode)
                                <x-ui.badge type="info" size="xs">{{ $item->criteriaNode->name ?? '' }}</x-ui.badge>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Footer -->
            <div class="mt-8 pt-6 border-t border-base-200">
                <div class="flex justify-end gap-3">
                    <button class="btn btn-ghost" disabled>Simpan Draft</button>
                    <button class="btn btn-primary" disabled>Submit Penilaian</button>
                </div>
                <p class="text-xs text-base-content/60 text-right mt-2">
                    * Field yang ditampilkan adalah preview dan tidak dapat diisi
                </p>
            </div>
        </x-ui.card>
        @else
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-20 h-20 mx-auto mb-4 text-base-content/30" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Form Belum Memiliki Konten</h3>
                <p class="text-base-content/60 mb-6">Tambahkan seksi dan item ke form untuk melihat preview.</p>
                <x-ui.button type="primary" href="{{ route('admin.kpi-forms.builder', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buat Form di Builder
                </x-ui.button>
            </div>
        </x-ui.card>
        @endif
    </div>

    <!-- Publish Modal -->
    @if($latestVersion && $latestVersion->status === 'draft')
    <x-ui.modal id="publish-modal" title="Publish Form">
        <p>Anda yakin ingin mempublish form <strong>{{ $template->name }}</strong> versi
            {{ $latestVersion->version ?? '1.0' }}?</p>
        <x-ui.alert type="warning" class="mt-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Form yang sudah dipublish tidak dapat diubah lagi.</span>
        </x-ui.alert>
        <x-slot:actions>
            <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
            <form method="POST" action="{{ route('admin.kpi-forms.publish-version', $latestVersion) }}">
                @csrf
                @method('PATCH')
                <x-ui.button type="success" :isSubmit="true">Ya, Publish</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.modal>
    @endif
</x-layouts.admin>