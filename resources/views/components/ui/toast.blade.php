{{--
    Toast Container Component
    For showing notifications/toasts
--}}

<div id="toast-container" class="toast toast-end toast-bottom z-[9999]">
    @if(session('success'))
        <div class="alert alert-success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>{{ session('info') }}</span>
        </div>
    @endif
</div>
