<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.kpi-forms.index') }}">Template Form KPI</a></li>
        <li>Form Builder</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Form Builder: {{ $template->name }}</h1>
                <p class="text-base-content/60">
                    Versi {{ $version->version }} •
                    <x-ui.badge type="{{ $version->status === 'published' ? 'success' : 'ghost' }}" size="xs">
                        {{ ucfirst($version->status) }}
                    </x-ui.badge>
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.kpi-forms.preview', $template) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Preview
                </x-ui.button>
                @if($version->status === 'draft')
                    <x-ui.button type="success" onclick="document.getElementById('publish-modal').showModal()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Publish
                    </x-ui.button>
                @endif
            </div>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6" x-data="formBuilder()">
        <!-- Toolbox -->
        <div class="lg:col-span-1 space-y-4">
            <x-ui.card title="Tambah Elemen" compact>
                <div class="space-y-2">
                    <button @click="addSection()" class="btn btn-outline btn-sm btn-block justify-start">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Seksi Baru
                    </button>
                </div>
            </x-ui.card>

            <x-ui.card title="Tipe Field" compact>
                <div class="space-y-2">
                    <div draggable="true" @dragstart="dragStart($event, 'numeric')" class="btn btn-ghost btn-sm btn-block justify-start cursor-move">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        Skor Numerik
                    </div>
                    <div draggable="true" @dragstart="dragStart($event, 'dropdown')" class="btn btn-ghost btn-sm btn-block justify-start cursor-move">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        Dropdown Skala
                    </div>
                    <div draggable="true" @dragstart="dragStart($event, 'radio')" class="btn btn-ghost btn-sm btn-block justify-start cursor-move">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Radio Button
                    </div>
                    <div draggable="true" @dragstart="dragStart($event, 'yesno')" class="btn btn-ghost btn-sm btn-block justify-start cursor-move">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Ya/Tidak
                    </div>
                    <div draggable="true" @dragstart="dragStart($event, 'textarea')" class="btn btn-ghost btn-sm btn-block justify-start cursor-move">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                        </svg>
                        Catatan/Komentar
                    </div>
                </div>
                <p class="text-xs text-base-content/60 mt-3">Drag & drop ke area form</p>
            </x-ui.card>

            <x-ui.card title="Kriteria" compact>
                <div class="space-y-1 max-h-64 overflow-y-auto">
                    @foreach($criteriaNodes ?? [] as $node)
                        <div
                            draggable="true"
                            @dragstart="dragStartCriteria($event, '{{ $node->id }}', '{{ $node->name }}')"
                            class="p-2 text-sm rounded hover:bg-base-200 cursor-move flex items-center gap-2"
                        >
                            @if($node->code)
                                <x-ui.badge type="ghost" size="xs">{{ $node->code }}</x-ui.badge>
                            @endif
                            <span>{{ Str::limit($node->name, 25) }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-base-content/60 mt-3">Drag kriteria ke seksi</p>
            </x-ui.card>
        </div>

        <!-- Form Canvas -->
        <div class="lg:col-span-3">
            <x-ui.card>
                <form method="POST" action="{{ route('admin.kpi-forms.save-builder', $template) }}" id="builder-form">
                    @csrf
                    <input type="hidden" name="form_data" x-model="JSON.stringify(formData)">

                    <!-- Sections -->
                    <div class="space-y-6" id="form-canvas">
                        @forelse($version->sections ?? [] as $sectionIndex => $section)
                            <div
                                class="border border-base-300 rounded-lg"
                                x-data="{ expanded: true }"
                                @dragover.prevent
                                @drop="dropItem($event, {{ $sectionIndex }})"
                            >
                                <!-- Section Header -->
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-t-lg cursor-pointer" @click="expanded = !expanded">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <div>
                                            <h3 class="font-bold">{{ $section->title }}</h3>
                                            @if($section->criteriaNode)
                                                <p class="text-sm text-base-content/60">Kriteria: {{ $section->criteriaNode->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2" @click.stop>
                                        <x-ui.badge type="ghost" size="sm">{{ $section->items->count() }} item</x-ui.badge>
                                        <button type="button" onclick="editSection('{{ $section->id }}')" class="btn btn-ghost btn-sm btn-circle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="deleteSection('{{ $section->id }}')" class="btn btn-ghost btn-sm btn-circle text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Section Items -->
                                <div x-show="expanded" x-collapse class="p-4 space-y-3">
                                    @forelse($section->items ?? [] as $item)
                                        <div class="flex items-start gap-3 p-3 bg-base-100 border border-base-200 rounded-lg hover:border-primary/50 transition-colors">
                                            <div class="flex-shrink-0 pt-1">
                                                <svg class="w-4 h-4 text-base-content/40 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium">{{ $item->label }}</span>
                                                    @if($item->is_required)
                                                        <span class="text-error">*</span>
                                                    @endif
                                                </div>
                                                @if($item->help_text)
                                                    <p class="text-sm text-base-content/60">{{ $item->help_text }}</p>
                                                @endif
                                                <div class="flex items-center gap-2 mt-2">
                                                    <x-ui.badge type="info" size="xs">{{ $item->field_type }}</x-ui.badge>
                                                    @if($item->criteriaNode)
                                                        <x-ui.badge type="ghost" size="xs">{{ $item->criteriaNode->code ?? $item->criteriaNode->name }}</x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0 flex gap-1">
                                                <button type="button" onclick="editItem('{{ $item->id }}')" class="btn btn-ghost btn-xs">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>
                                                <button type="button" onclick="deleteItem('{{ $item->id }}')" class="btn btn-ghost btn-xs text-error">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-8 text-base-content/60 border-2 border-dashed border-base-300 rounded-lg">
                                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            <p class="text-sm">Drag & drop field atau kriteria ke sini</p>
                                        </div>
                                    @endforelse

                                    <!-- Add Item Button -->
                                    <button type="button" onclick="addItem('{{ $section->id }}')" class="btn btn-ghost btn-sm btn-block border-2 border-dashed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Tambah Item
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-base-content/60 border-2 border-dashed border-base-300 rounded-lg">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg">Form masih kosong</p>
                                <p class="text-sm mb-4">Mulai dengan menambahkan seksi</p>
                                <button type="button" @click="addSection()" class="btn btn-primary">
                                    Tambah Seksi Pertama
                                </button>
                            </div>
                        @endforelse
                    </div>

                    @if(($version->sections ?? collect())->count() > 0)
                        <div class="flex justify-end mt-6 pt-6 border-t border-base-200">
                            <x-ui.button type="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Simpan Form
                            </x-ui.button>
                        </div>
                    @endif
                </form>
            </x-ui.card>
        </div>
    </div>

    <!-- Add Section Modal -->
    <x-ui.modal id="add-section-modal" title="Tambah Seksi">
        <form method="POST" action="{{ route('admin.kpi-forms.add-section', $version) }}" class="space-y-4">
            @csrf
            <x-ui.input name="title" label="Judul Seksi" required />
            <x-ui.textarea name="description" label="Deskripsi" rows="2" />
            <x-ui.select
                name="criteria_node_id"
                label="Kriteria (opsional)"
                :options="collect($criteriaNodes ?? [])->mapWithKeys(fn($n) => [$n->id => $n->name])->toArray()"
            />
            <x-slot:actions>
                <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
                <x-ui.button type="primary">Simpan</x-ui.button>
            </x-slot:actions>
        </form>
    </x-ui.modal>

    <!-- Add Item Modal -->
    <x-ui.modal id="add-item-modal" title="Tambah Item" size="lg">
        <form method="POST" action="{{ route('admin.kpi-forms.add-item', $version) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="section_id" id="add-item-section-id">

            <x-ui.input name="label" label="Label" required />
            <x-ui.textarea name="help_text" label="Teks Bantuan" rows="2" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.select
                    name="field_type"
                    label="Tipe Field"
                    :options="[
                        'numeric' => 'Skor Numerik',
                        'dropdown' => 'Dropdown Skala',
                        'radio' => 'Radio Button',
                        'yesno' => 'Ya/Tidak',
                        'textarea' => 'Catatan'
                    ]"
                    required
                    :searchable="false"
                />
                <x-ui.select
                    name="criteria_node_id"
                    label="Kriteria"
                    :options="collect($criteriaNodes ?? [])->mapWithKeys(fn($n) => [$n->id => $n->name])->toArray()"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-ui.input name="min_value" label="Nilai Minimum" type="number" />
                <x-ui.input name="max_value" label="Nilai Maximum" type="number" />
            </div>

            <x-ui.checkbox
                name="is_required"
                :options="[['value' => '1', 'label' => 'Wajib diisi']]"
                :checked="['1']"
                :single="true"
            />

            <x-slot:actions>
                <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
                <x-ui.button type="primary">Simpan</x-ui.button>
            </x-slot:actions>
        </form>
    </x-ui.modal>

    <!-- Publish Modal -->
    <x-ui.modal id="publish-modal" title="Publish Form">
        <p>Anda yakin ingin mempublish form <strong>{{ $template->name }}</strong> versi {{ $version->version }}?</p>
        <p class="text-sm text-base-content/60 mt-2">Form yang sudah dipublish tidak dapat diubah lagi.</p>
        <x-slot:actions>
            <form method="dialog"><button class="btn btn-ghost">Batal</button></form>
            <form method="POST" action="{{ route('admin.kpi-forms.publish', $version) }}">
                @csrf
                @method('PATCH')
                <x-ui.button type="success">Publish</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.modal>

    <script>
        function formBuilder() {
            return {
                formData: @json($version->toArray()),
                draggedType: null,
                draggedCriteria: null,

                addSection() {
                    document.getElementById('add-section-modal').showModal();
                },

                dragStart(event, type) {
                    this.draggedType = type;
                    event.dataTransfer.effectAllowed = 'copy';
                },

                dragStartCriteria(event, id, name) {
                    this.draggedCriteria = { id, name };
                    event.dataTransfer.effectAllowed = 'copy';
                },

                dropItem(event, sectionIndex) {
                    if (this.draggedType || this.draggedCriteria) {
                        // Handle drop logic
                        console.log('Dropped:', this.draggedType, this.draggedCriteria, 'to section', sectionIndex);
                    }
                    this.draggedType = null;
                    this.draggedCriteria = null;
                }
            }
        }

        function addItem(sectionId) {
            document.getElementById('add-item-section-id').value = sectionId;
            document.getElementById('add-item-modal').showModal();
        }

        function editSection(sectionId) {
            // Implement edit section logic
            console.log('Edit section:', sectionId);
        }

        function deleteSection(sectionId) {
            if (confirm('Yakin ingin menghapus seksi ini?')) {
                // Implement delete section logic
                console.log('Delete section:', sectionId);
            }
        }

        function editItem(itemId) {
            // Implement edit item logic
            console.log('Edit item:', itemId);
        }

        function deleteItem(itemId) {
            if (confirm('Yakin ingin menghapus item ini?')) {
                // Implement delete item logic
                console.log('Delete item:', itemId);
            }
        }
    </script>
</x-layouts.admin>
