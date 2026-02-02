<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.criteria.index') }}">Kriteria</a></li>
        <li>Edit Kriteria - {{ $node->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Edit Kriteria</h1>
                <p class="text-base-content/60">Perbarui informasi kriteria {{ $node->name }}</p>
            </div>
            <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card>
                <form method="POST" action="{{ route('admin.criteria.update-node', $node) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Informasi Kriteria</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.select name="node_type" label="Tipe"
                                :options="['criteria' => 'Kriteria', 'subcriteria' => 'Sub-kriteria', 'indicator' => 'Indikator']"
                                selected="{{ old('node_type', $node->node_type) }}" :searchable="false" required />
                            <x-ui.input name="code" label="Kode" placeholder="C1, C1.1, dll"
                                value="{{ old('code', $node->code) }}" error="{{ $errors->first('code') }}" />
                        </div>
                        <div class="mt-4">
                            <x-ui.input name="name" label="Nama" placeholder="Nama kriteria"
                                value="{{ old('name', $node->name) }}" error="{{ $errors->first('name') }}" required />
                        </div>
                    </div>

                    <div class="border-b border-base-200 pb-6">
                        <h3 class="text-lg font-medium mb-4">Deskripsi</h3>
                        <x-ui.textarea name="description" label="Deskripsi Kriteria"
                            placeholder="Deskripsi kriteria (opsional)"
                            value="{{ old('description', $node->description) }}"
                            error="{{ $errors->first('description') }}" rows="4" />
                    </div>



                    <div class="flex justify-end gap-3">
                        <x-ui.button type="ghost" href="{{ route('admin.criteria.index') }}">Batal</x-ui.button>
                        <x-ui.button type="primary" :isSubmit="true">Update Kriteria</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card title="Informasi">
                <div class="space-y-4">
                    <div class="bg-info/10 p-3 rounded-lg">
                        <p class="text-sm font-medium text-info mb-1">Kriteria Saat Ini</p>
                        <p class="text-sm">{{ $node->name }}</p>
                        <p class="text-sm text-base-content/60">{{ $node->node_type }}</p>
                        @if($node->code)
                        <span class="badge badge-primary badge-sm mt-1">{{ $node->code }}</span>
                        @endif
                    </div>
                    @if($node->parent)
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Parent</p>
                        <p class="text-sm">{{ $node->parent->name }}</p>
                    </div>
                    @endif
                    @if($node->children->count() > 0)
                    <div class="bg-base-100 p-3 rounded-lg border">
                        <p class="text-sm font-medium mb-1">Sub-kriteria ({{ $node->children->count() }})</p>
                        <div class="text-xs space-y-1">
                            @foreach($node->children as $child)
                            <p>• {{ $child->name }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="alert alert-warning">
                        <p class="text-sm">Perubahan pada kriteria akan mempengaruhi penilaian yang menggunakan set
                            kriteria ini.</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
