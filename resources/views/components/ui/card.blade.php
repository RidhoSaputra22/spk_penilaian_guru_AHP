{{--
    Reusable Card Component

    @param string $title - Card title (optional)
    @param string $class - Additional CSS classes (optional)
    @param bool $compact - Use compact padding (optional)
--}}

@props([
    'title' => null,
    'class' => '',
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl ' . $class]) }}>
    <div class="card-body {{ $compact ? 'p-4' : '' }}">
        @if($title)
            <h2 class="card-title">{{ $title }}</h2>
        @endif
        {{ $slot }}
        @if(isset($actions))
            <div class="card-actions justify-end mt-4">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
