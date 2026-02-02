{{--
    Reusable Button Component

    @param string $type - Button type: primary, secondary, accent, ghost, link, info, success, warning, error
    @param string $size - Button size: xs, sm, md, lg
    @param bool $outline - Outline style
    @param bool $loading - Show loading state
    @param string $href - If set, renders as anchor tag
    @param bool $disabled - Disabled state
--}}

@props([
'type' => 'primary',
'size' => 'md',
'outline' => false,
'loading' => false,
'href' => null,
'disabled' => false,
'isSubmit' => true,

])

@php
$typeClass = match($type) {
'primary' => 'btn-primary',
'secondary' => 'btn-secondary',
'accent' => 'btn-accent',
'ghost' => 'btn-ghost',
'link' => 'btn-link',
'info' => 'btn-info',
'success' => 'btn-success',
'warning' => 'btn-warning',
'error' => 'btn-error',
'neutral' => 'btn-neutral',
default => 'btn-primary',
};

$sizeClass = match($size) {
'xs' => 'btn-xs',
'sm' => 'btn-sm',
'md' => '',
'lg' => 'btn-lg',
default => '',
};

$classes = "btn {$typeClass} {$sizeClass}";

if ($outline) {
$classes .= ' btn-outline';
}

if ($loading) {
$classes .= ' loading';
}
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
@else
<button {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}
    type="{{ $isSubmit ? 'submit' : 'button' }}">
    @if($loading)
    <span class="loading loading-spinner loading-sm"></span>
    @endif
    {{ $slot }}
</button>
@endif
