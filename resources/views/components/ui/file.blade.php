{{--
    Reusable File Upload Component

    @param string $name - Input name
    @param string $label - Label text
    @param string $accept - Accepted file types (e.g., "image/*", "application/pdf")
    @param bool $required - Required field
    @param string $helpText - Helper text below input
    @param bool $preview - Show image preview for image uploads
    @param string $currentFile - URL of current file (for edit forms)
--}}

@props([
    'name',
    'label' => null,
    'accept' => '',
    'required' => false,
    'helpText' => null,
    'preview' => false,
    'currentFile' => null,
])

@php
    $inputId = $name . '_' . uniqid();
    $isImage = str_contains($accept, 'image');
@endphp

<div class="form-control w-full" x-data="{
    preview: @js($currentFile),
    fileName: '',
    updatePreview(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            if (@js($isImage && $preview)) {
                const reader = new FileReader();
                reader.onload = (e) => { this.preview = e.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }
}">
    @if($label)
    <label class="label" for="{{ $inputId }}">
        <span class="label-text">
            {{ $label }}
            @if($required)
            <span class="text-error">*</span>
            @endif
        </span>
    </label>
    @endif

    <!-- File Input -->
    <input
        type="file"
        id="{{ $inputId }}"
        name="{{ $name }}"
        accept="{{ $accept }}"
        @change="updatePreview"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'file-input file-input-bordered w-full']) }}
    />

    <!-- Preview for Images -->
    @if($isImage && $preview)
    <div x-show="preview" class="mt-3">
        <img :src="preview" class="w-32 h-32 object-cover rounded-lg border border-base-300" alt="Preview">
    </div>
    @endif

    <!-- File Name Display -->
    <div x-show="fileName" class="mt-2 text-sm text-base-content/70" x-text="'File: ' + fileName"></div>

    @if($helpText)
    <label class="label">
        <span class="label-text-alt text-base-content/70">{{ $helpText }}</span>
    </label>
    @endif

    @error($name)
    <label class="label">
        <span class="label-text-alt text-error">{{ $message }}</span>
    </label>
    @enderror

    <!-- Current File (for edit forms) -->
    @if($currentFile)
    <div class="mt-2">
        <label class="label">
            <span class="label-text-alt">File saat ini:</span>
        </label>
        @if($isImage)
        <img src="{{ $currentFile }}" class="w-32 h-32 object-cover rounded-lg border border-base-300" alt="Current file">
        @else
        <a href="{{ $currentFile }}" target="_blank" class="link link-primary text-sm">
            Lihat file
        </a>
        @endif
    </div>
    @endif
</div>
