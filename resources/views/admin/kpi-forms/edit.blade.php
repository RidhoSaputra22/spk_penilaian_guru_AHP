@extends('layouts.admin')

@section('title', 'Edit Template KPI')

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
            <h1 class="text-2xl font-bold">Edit Template KPI</h1>
            <p class="text-base-content/70">Perbarui informasi dasar template form KPI</p>
        </div>
    </div>

    <!-- Edit Form -->
    <x-ui.card title="Informasi Template">
        <form method="POST" action="{{ route('admin.kpi-forms.update', $template) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Template Name -->
                <div class="lg:col-span-2">
                    <label class="label">
                        <span class="label-text font-semibold">Nama Template <span class="text-error">*</span></span>
                    </label>
                    <x-ui.input
                        type="text"
                        name="name"
                        value="{{ old('name', $template->name) }}"
                        placeholder="Masukkan nama template..."
                        required
                        :error="$errors->first('name')"
                    />
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Description -->
                <div class="lg:col-span-2">
                    <label class="label">
                        <span class="label-text font-semibold">Deskripsi</span>
                    </label>
                    <x-ui.textarea
                        name="description"
                        rows="4"
                        placeholder="Masukkan deskripsi template..."
                        :error="$errors->first('description')"
                    >{{ old('description', $template->description) }}</x-ui.textarea>
                    @error('description')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Template Info -->
                <div class="lg:col-span-2">
                    <x-ui.alert type="info">
                        <strong>Catatan:</strong> Perubahan ini hanya mempengaruhi informasi dasar template.
                        Untuk mengubah struktur form, gunakan <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="link link-primary">Form Builder</a>.
                    </x-ui.alert>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="{{ route('admin.kpi-forms.index') }}" class="btn btn-outline">
                    Batal
                </a>
                <x-ui.button variant="primary" type="submit">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <!-- Template Information -->
    <x-ui.card title="Informasi Template">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="label">
                    <span class="label-text font-semibold">ID Template</span>
                </label>
                <div class="text-sm font-mono bg-base-200 px-3 py-2 rounded">
                    {{ $template->id }}
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Status</span>
                </label>
                <x-ui.badge variant="info">
                    Draft
                </x-ui.badge>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Dibuat</span>
                </label>
                <div class="text-sm">
                    {{ $template->created_at->format('d M Y H:i') }}
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Terakhir Diperbarui</span>
                </label>
                <div class="text-sm">
                    {{ $template->updated_at->format('d M Y H:i') }}
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Jumlah Versi</span>
                </label>
                <div class="text-sm">
                    {{ $template->versions()->count() }} versi
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Versi Aktif</span>
                </label>
                <div class="text-sm">
                    v{{ $template->versions()->latest('version')->first()?->version ?? '1.0' }}
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Quick Actions -->
    <x-ui.card title="Aksi Cepat">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.kpi-forms.builder', $template) }}" class="btn btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Struktur Form
            </a>

            <a href="{{ route('admin.kpi-forms.preview', $template) }}" class="btn btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Preview Form
            </a>

            <a href="{{ route('admin.kpi-forms.versions', $template) }}" class="btn btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Versi
            </a>

            <button onclick="cloneTemplateModal.showModal()" class="btn btn-outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Duplikat Template
            </button>
        </div>
    </x-ui.card>
</div>

<!-- Clone Template Modal -->
<x-ui.modal id="cloneTemplateModal" title="Duplikat Template">
    <form method="POST" action="{{ route('admin.kpi-forms.clone', $template) }}" class="space-y-4">
        @csrf
        <div>
            <p class="text-sm mb-4">
                Template akan diduplikat dengan nama "{{ $template->name }} (Copy)".
                Anda dapat mengubah nama setelah duplikasi selesai.
            </p>
            <x-ui.alert type="info">
                <strong>Catatan:</strong> Duplikasi akan menyalin struktur template tanpa data penilaian yang sudah ada.
            </x-ui.alert>
        </div>

        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="cloneTemplateModal.close()">
                Batal
            </x-ui.button>
            <x-ui.button variant="primary" type="submit">
                Duplikat
            </x-ui.button>
        </x-slot>
    </form>
</x-ui.modal>
@endsection
