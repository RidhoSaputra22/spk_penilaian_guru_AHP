{{--
    Reusable Searchable Multi-Select Component

    @param string $name - Input name (will be submitted as array: name[])
    @param string $label - Label text
    @param array $options - Options array: [value => label] OR [['value' => x, 'label' => y]]
    @param array $selected - Selected values array
    @param string $placeholder - Placeholder text
    @param string $error - Error message
    @param bool $required - Required field
    @param string $searchPlaceholder - Search input placeholder
    @param string $hint - Helper text below field
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => [],
    'placeholder' => 'Pilih...',
    'error' => null,
    'required' => false,
    'searchPlaceholder' => 'Cari...',
    'hint' => 'Pilih satu atau lebih',
])

@php
    // Get old values or use selected prop
    $selectedValues = old($name, $selected);
    if (!is_array($selectedValues)) {
        $selectedValues = [];
    }

    // Normalize options to consistent format
    $normalizedOptions = [];
    foreach ($options as $key => $option) {
        if (is_array($option) && isset($option['value']) && isset($option['label'])) {
            $normalizedOptions[] = ['value' => $option['value'], 'label' => $option['label']];
        } else {
            $normalizedOptions[] = ['value' => $key, 'label' => $option];
        }
    }
@endphp

<div class="form-control w-full">
    @if($label)
        <label class="label">
            <span class="label-text">
                {{ $label }}
                @if($required)
                    <span class="text-error">*</span>
                @endif
            </span>
        </label>
    @endif

    <div x-data="{
        open: false,
        searchTerm: '',
        selectedValues: @js($selectedValues),
        options: @js($normalizedOptions),
        get filteredOptions() {
            if (!this.searchTerm) return this.options;
            return this.options.filter(option =>
                option.label.toLowerCase().includes(this.searchTerm.toLowerCase())
            );
        },
        get selectedOptions() {
            return this.options.filter(opt => this.selectedValues.includes(opt.value));
        },
        toggleOption(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            } else {
                this.selectedValues.push(value);
            }
        },
        isSelected(value) {
            return this.selectedValues.includes(value);
        },
        removeOption(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            }
        }
    }"
    @click.outside="open = false"
    class="relative">

        {{-- Hidden inputs for form submission --}}
        <template x-for="value in selectedValues" :key="value">
            <input type="hidden" :name="'{{ $name }}[]'" :value="value">
        </template>

        {{-- Display/Trigger Button --}}
        <button
            type="button"
            @click="open = !open"
            {{ $attributes->merge(['class' => 'min-h-12 select select-bordered w-full flex items-center justify-between' . ($error ? ' select-error' : '')]) }}
        >
            <span x-show="selectedValues.length === 0" class="text-base-content/40">{{ $placeholder }}</span>
            <span x-show="selectedValues.length > 0" class="text-sm" x-text="selectedValues.length + ' item dipilih'"></span>
            <svg class="w-5 h-5 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Selected Items Display --}}
        <div x-show="selectedValues.length > 0" class="mt-2 p-3 bg-base-200 rounded-lg">
            <div class="flex flex-wrap gap-2">
                <template x-for="option in selectedOptions" :key="option.value">
                    <span class="badge badge-primary gap-2">
                        <span x-text="option.label" class="text-xs"></span>
                        <button type="button" @click.stop="removeOption(option.value)" class="hover:text-error">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>

        {{-- Dropdown --}}
        <div x-show="open"
            x-transition
            class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg">

            {{-- Search Input --}}
            <div class="p-2 border-b border-base-300">
                <div class="relative">
                    <input
                        type="text"
                        x-model="searchTerm"
                        :placeholder="@js($searchPlaceholder)"
                        class="input input-sm input-bordered w-full pr-8"
                        @click.stop
                    >
                    <svg class="w-4 h-4 absolute right-2.5 top-2.5 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Options List --}}
            <div class="max-h-60 overflow-y-auto">
                <template x-for="option in filteredOptions" :key="option.value">
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-base-200 cursor-pointer transition-colors">
                        <input
                            type="checkbox"
                            :checked="isSelected(option.value)"
                            @change="toggleOption(option.value)"
                            class="checkbox checkbox-sm checkbox-primary"
                            @click.stop
                        >
                        <span class="label-text" x-text="option.label"></span>
                    </label>
                </template>

                <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-center text-base-content/60 text-sm">
                    Tidak ada hasil
                </div>
            </div>
        </div>
    </div>

    @if($hint)
        <label class="label">
            <span class="label-text-alt">{{ $hint }}</span>
        </label>
    @endif

    @if($error)
        <label class="label">
            <span class="label-text-alt text-error">{{ $error }}</span>
        </label>
    @endif

    @error($name)
        <label class="label">
            <span class="label-text-alt text-error">{{ $message }}</span>
        </label>
    @enderror
</div>
