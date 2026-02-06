<x-layouts.teacher>
    <x-slot:title>Detail Status - {{ $assessment->period->name ?? 'Penilaian' }}</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('teacher.status.index') }}">Status Penilaian</a></li>
        <li>Detail</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Status Penilaian</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $assessment->period->name ?? '' }} - {{ $assessment->period->academic_year ?? '' }}
                </p>
            </div>
            <x-ui.button type="ghost" href="{{ route('teacher.status.index') }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Assessment Info -->
        <x-ui.card title="Informasi Penilaian">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-base-content/60">Status:</span>
                    @switch($assessment->status)
                    @case('pending')
                    <x-ui.badge type="ghost">Pending</x-ui.badge>
                    @break
                    @case('draft')
                    @case('in_progress')
                    <x-ui.badge type="warning">Sedang Dinilai</x-ui.badge>
                    @break
                    @case('submitted')
                    <x-ui.badge type="info">Submitted</x-ui.badge>
                    @break
                    @case('finalized')
                    <x-ui.badge type="success">Final</x-ui.badge>
                    @break
                    @endswitch
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Periode:</span>
                    <span class="font-medium">{{ $assessment->period->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Tahun Akademik:</span>
                    <span>{{ $assessment->period->academic_year ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Form KPI:</span>
                    <span>{{ $assessment->assignment->formVersion->template->name ?? '-' }}</span>
                </div>
            </div>
        </x-ui.card>

        <!-- Assessor Info -->
        <x-ui.card title="Informasi Penilai">
            <div class="flex items-start gap-4">
                <div class="avatar placeholder">
                    <div class="bg-secondary text-secondary-content rounded-full w-16 flex justify-center items-center">
                        <span class="text-2xl">{{ substr($assessment->assessor->user->name ?? '?', 0, 1) }}</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ $assessment->assessor->user->name ?? '-' }}</h3>
                    <p class="text-sm text-base-content/60">{{ $assessment->assessor->title ?? 'Tim Penilai' }}</p>
                    <div class="mt-2 text-sm space-y-1">
                        <div>
                            <span class="text-base-content/60">Mulai Dinilai:</span>
                            <span class="ml-1">{{ $assessment->started_at?->format('d M Y H:i') ?? '-' }}</span>
                        </div>
                        @if($assessment->submitted_at)
                        <div>
                            <span class="text-base-content/60">Tanggal Submit:</span>
                            <span class="ml-1">{{ $assessment->submitted_at->format('d M Y H:i') }}</span>
                        </div>
                        @endif
                        @if($assessment->finalized_at)
                        <div>
                            <span class="text-base-content/60">Tanggal Final:</span>
                            <span class="ml-1">{{ $assessment->finalized_at->format('d M Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>

    <!-- Timeline -->
    <x-ui.card title="Timeline Penilaian">
        <ul class="timeline timeline-horizontal">
            <li>
                <div class="timeline-start timeline-box">Penilaian Dibuat</div>
                <div class="timeline-middle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-success">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="timeline-end text-sm text-base-content/60">
                    {{ $assessment->created_at?->format('d M Y H:i') ?? '-' }}
                </div>
                <hr class="{{ $assessment->started_at ? 'bg-success' : '' }}" />
            </li>
            <li>
                <hr class="{{ $assessment->started_at ? 'bg-success' : '' }}" />
                <div class="timeline-start timeline-box {{ $assessment->started_at ? '' : 'opacity-50' }}">
                    Mulai Dinilai
                </div>
                <div class="timeline-middle">
                    @if($assessment->started_at)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-success">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-base-content/30">
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                    @endif
                </div>
                <div class="timeline-end text-sm text-base-content/60">
                    {{ $assessment->started_at?->format('d M Y H:i') ?? 'Menunggu' }}
                </div>
                <hr class="{{ $assessment->submitted_at ? 'bg-success' : '' }}" />
            </li>
            <li>
                <hr class="{{ $assessment->submitted_at ? 'bg-success' : '' }}" />
                <div class="timeline-start timeline-box {{ $assessment->submitted_at ? '' : 'opacity-50' }}">
                    Submitted
                </div>
                <div class="timeline-middle">
                    @if($assessment->submitted_at)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-success">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-base-content/30">
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                    @endif
                </div>
                <div class="timeline-end text-sm text-base-content/60">
                    {{ $assessment->submitted_at?->format('d M Y H:i') ?? 'Menunggu' }}
                </div>
                <hr class="{{ $assessment->finalized_at ? 'bg-success' : '' }}" />
            </li>
            <li>
                <hr class="{{ $assessment->finalized_at ? 'bg-success' : '' }}" />
                <div class="timeline-start timeline-box {{ $assessment->finalized_at ? '' : 'opacity-50' }}">
                    Finalisasi
                </div>
                <div class="timeline-middle">
                    @if($assessment->finalized_at)
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-success">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-base-content/30">
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                    @endif
                </div>
                <div class="timeline-end text-sm text-base-content/60">
                    {{ $assessment->finalized_at?->format('d M Y H:i') ?? 'Menunggu' }}
                </div>
            </li>
        </ul>
    </x-ui.card>

    <!-- Notes (if finalized and allowed) -->
    @if($assessment->status === 'finalized' && $assessment->assessor_notes)
    <x-ui.card title="Catatan dari Penilai" class="mt-6">
        <div class="prose max-w-none">
            {{ $assessment->assessor_notes }}
        </div>
    </x-ui.card>
    @endif

</x-layouts.teacher>
