<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Kriteria & Indikator</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Kriteria & Indikator</h1>
                <p class="text-base-content/60">Kelola kriteria dan sub-kriteria penilaian</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="primary" href="{{ route('admin.criteria.sets.create') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Buat Set Baru
                </x-ui.button>

            </div>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Criteria Sets List -->
        <div class="lg:col-span-1 ">
            <x-ui.card title="Set Kriteria">
                <div class="space-y-2">
                    @forelse($criteriaSets ?? [] as $set)
                    <a href="{{ route('admin.criteria.index', ['set' => $set->id]) }}"
                        class="flex items-center justify-between p-3 rounded-lg hover:bg-primary hover:text-white  transition-colors {{ ($currentSet->id ?? null) === $set->id ? 'bg-primary text-white' : '' }}">
                        <div>
                            <div class="font-medium">{{ $set->name }}</div>
                            <div class="text-sm ">v{{ $set->version }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($set->is_active)
                            <x-ui.badge type="success" size="xs">Active</x-ui.badge>
                            @endif
                            @if($set->locked_at)
                            <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="text-center py-8 text-base-content/60">
                        <p>Belum ada set kriteria</p>
                        <x-ui.button type="primary" size="sm" href="{{ route('admin.criteria.sets.create') }}"
                            class="mt-2">
                            Buat Set Pertama
                        </x-ui.button>
                    </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <!-- Criteria Tree -->
        <div class="lg:col-span-3">
            @if(isset($currentSet))
            <x-ui.card class="min-h-130">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-bold">{{ $currentSet->name }}</h3>
                        <p class="text-sm text-base-content/60">Version {{ $currentSet->version }} •
                            {{ $criteriaNodes->count() }} kriteria</p>
                    </div>
                    <div class="flex gap-2">
                        @if(!$currentSet->locked_at)
                        <x-ui.button type="warning" size="sm"
                            onclick="document.getElementById('lock-set-modal').showModal()">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Kunci Set
                        </x-ui.button>
                        @endif
                        <x-ui.button type="primary" size="sm"
                            href="{{ route('admin.criteria.sets.edit', $currentSet) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </x-ui.button>
                        <x-ui.button type="primary" size="sm" class="flex items-center justify-center"
                            href="{{ route('admin.criteria.add', ['set' => $currentSet->id ?? ''])}}">
                            <svg class="w-4 h-4 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </x-ui.button>

                    </div>
                </div>

                <!-- Criteria Tree View -->
                <div class="space-y-2" x-data="{ expandedNodes: [] }">
                    @forelse($criteriaNodes->where('parent_id', null) ?? [] as $criteria)
                    <div class="border border-base-200 rounded-lg">
                        <!-- Parent Criteria -->
                        <div class="flex items-center justify-between p-4 hover:bg-base-200/50 cursor-pointer"
                            @click="expandedNodes.includes('{{ $criteria->id }}') ? expandedNodes = expandedNodes.filter(id => id !== '{{ $criteria->id }}') : expandedNodes.push('{{ $criteria->id }}')">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 transition-transform"
                                    :class="expandedNodes.includes('{{ $criteria->id }}') ? 'rotate-90' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                                <div>
                                    <div class="flex items-center gap-2">
                                        @if($criteria->code)
                                        <x-ui.badge type="primary" size="sm">{{ $criteria->code }}</x-ui.badge>
                                        @endif
                                        <span class="font-medium">{{ $criteria->name }}</span>
                                    </div>
                                    @if($criteria->description)
                                    <p class="text-sm text-base-content/60 mt-1">
                                        {{ Str::limit($criteria->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.badge type="ghost" size="sm">{{ $criteria->children->count() }} sub</x-ui.badge>
                                @if(!$currentSet->locked_at)
                                <div class="dropdown dropdown-end" @click.stop>
                                    <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 5v.01M12 12v.01M12 19v.01" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0"
                                        class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                                        <li><a href="{{ route('admin.criteria.edit', $criteria) }}">Edit</a></li>
                                        <li>
                                            <a
                                                href="{{ route('admin.criteria.add', ['set' => $currentSet->id ?? '', 'parent' => $criteria->id]) }}">
                                                Tambah Sub-kriteria
                                            </a>
                                        </li>
                                        <li><a class="text-error"
                                                onclick="deleteCriteria('{{ $criteria->id }}')">Hapus</a></li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Sub Criteria -->
                        <div x-show="expandedNodes.includes('{{ $criteria->id }}')" x-collapse>
                            <div class="border-t border-base-200 bg-base-200/30">
                                @forelse($criteria->children as $subCriteria)
                                <div
                                    class="flex items-center justify-between p-4 pl-12 border-b border-base-200 last:border-b-0 hover:bg-base-200/50">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-base-content/40" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                        </svg>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                @if($subCriteria->code)
                                                <x-ui.badge type="secondary" size="xs">{{ $subCriteria->code }}
                                                </x-ui.badge>
                                                @endif
                                                <span>{{ $subCriteria->name }}</span>
                                            </div>
                                            @if($subCriteria->description)
                                            <p class="text-sm text-base-content/60">
                                                {{ Str::limit($subCriteria->description, 80) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$currentSet->locked_at)
                                    <div class="flex justify-center items-center gap-1">
                                        <a href="{{ route('admin.criteria.edit', $subCriteria) }}"
                                            class="btn btn-ghost btn-xs flex items-center justify-center mt-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button onclick="deleteCriteria('{{ $subCriteria->id }}')"
                                            class="btn btn-ghost btn-xs flex items-center justify-center text-error">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="p-4 pl-12 text-center text-base-content/60 text-sm">
                                    Belum ada sub-kriteria
                                </div>
                                @endforelse

                                @if(!$currentSet->locked_at)
                                <div class="p-2 pl-12 flex">
                                    <a href="{{ route('admin.criteria.add', ['set' => $currentSet->id ?? '', 'parent' => $criteria->id]) }}"
                                        class="btn btn-ghost btn-sm inline-flex items-center justify-start">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Tambah Sub-kriteria
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 text-base-content/60">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-lg">Belum ada kriteria</p>
                        <p class="text-sm mb-4">Mulai dengan menambahkan kriteria utama</p>
                        <x-ui.button type="primary"
                            href="{{ route('admin.criteria.add', ['set' => $currentSet->id ?? ''])}}">
                            Tambah Kriteria Pertama
                        </x-ui.button>
                    </div>
                    @endforelse
                </div>
            </x-ui.card>
            @else
            <x-ui.card class="min-h-130">
                <div class="text-center py-12 text-base-content/60">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-lg">Pilih Set Kriteria</p>
                    <p class="text-sm">Atau buat set kriteria baru untuk memulai</p>
                </div>
            </x-ui.card>
            @endif
        </div>
    </div>



    <!-- Lock Set Modal -->
    @if(isset($currentSet))
    <x-ui.modal id="lock-set-modal" title="Kunci Set Kriteria">
        <p>Anda yakin ingin mengunci set kriteria <strong>{{ $currentSet->name }}</strong>?</p>
        <p class="text-sm text-base-content mt-2">Setelah dikunci, kriteria tidak dapat diubah lagi.</p>
        <x-slot:actions>
            <form method="dialog">
                <button class="btn btn-ghost">Batal</button>
            </form>
            <form method="POST" action="{{ route('admin.criteria.sets.lock', $currentSet) }}">
                @csrf
                @method('PATCH')
                <x-ui.button type="warning">Kunci</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.modal>
    @endif

    <script>
    function addSubCriteria(parentId, parentName) {
        document.getElementById('parent_id_input').value = parentId;
        document.getElementById('parent-name-display').textContent = parentName;
        document.getElementById('parent-info').classList.remove('hidden');
        document.getElementById('add-criteria-modal').showModal();
    }

    function deleteCriteria(id) {
        if (confirm('Yakin ingin menghapus kriteria ini? ini juga akan menghapus semua sub-kriteria di dalamnya.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/criteria/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</x-layouts.admin>
