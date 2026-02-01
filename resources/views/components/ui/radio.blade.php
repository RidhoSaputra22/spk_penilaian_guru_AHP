{{--
    Reusable Radio Component

    @param string $name - Input name
    @param string $label - Label text
    @param array $options - Array of options [['value' => 'val', 'label' => 'Label']]
    @param string $value - Currently selected value
    @param bool $required - Required field
    @param string $layout - Layout direction: 'horizontal' or 'vertical'
    @param string $helpText - Helper text below radio group
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => '',
    'required' => false,
    'layout' => 'horizontal',
    'helpText' => null,
])

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

    <div class="flex {{ $layout === 'vertical' ? 'flex-col' : 'flex-wrap' }} gap-4">
        @foreach($options as $option)
        @php
            $isDisabled = isset($option['disabled']) && $option['disabled'];
        @endphp
        <label class="flex items-center gap-2 cursor-pointer {{ $isDisabled ? 'opacity-50' : '' }}">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $option['value'] }}"
                {{ old($name, $value) == $option['value'] ? 'checked' : '' }}
                {{ $required ? 'required' : '' }}
                {{ $isDisabled ? 'disabled' : '' }}
                {{ $attributes->merge(['class' => 'radio radio-primary']) }}
            />
            <span>{{ $option['label'] }}</span>
        </label>
        @endforeach
    </div>

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
</div>
