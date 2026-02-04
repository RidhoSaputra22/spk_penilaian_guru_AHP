<x-layouts.assessor>
    <x-slot:title>Penilaian KPI</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Penilaian KPI Guru</h1>
                <p class="text-base-content/70 mt-1">Pilih periode untuk memulai penilaian</p>
            </div>
        </div>
    </x-slot:header>

    @if($periods->isEmpty())
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Tidak Ada Periode</h3>
                <p class="text-base-content/60">Anda belum ditugaskan untuk menilai pada periode manapun.</p>
            </div>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($periods as $period)
                <x-ui.card class="hover:shadow-2xl transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold">{{ $period->name }}</h3>
                            <p class="text-sm text-base-content/60">
                                {{ $period->academic_year }} - Semester {{ $period->semester }}
                            </p>
                        </div>
                        @if($period->status === 'open')
                            <x-ui.badge type="success" size="sm">Aktif</x-ui.badge>
                        @elseif($period->status === 'closed')
                            <x-ui.badge type="error" size="sm">Ditutup</x-ui.badge>
                        @elseif($period->status === 'archived')
                            <x-ui.badge type="ghost" size="sm">Arsip</x-ui.badge>
                        @elseif($period->status === 'draft')
                            <x-ui.badge type="warning" size="sm">Draft</x-ui.badge>
                        @else
                            <x-ui.badge type="info" size="sm">{{ ucfirst($period->status) }}</x-ui.badge>
                        @endif
                    </div>

                    <div class="space-y-2 text-sm">
                        @if($period->scoring_open_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Dibuka:</span>
                                <span>{{ $period->scoring_open_at->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if($period->scoring_close_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Ditutup:</span>
                                <span class="{{ $period->scoring_close_at->isPast() ? 'text-error' : '' }}">
                                    {{ $period->scoring_close_at->format('d M Y') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <x-slot:actions>
                        <x-ui.button type="primary" href="{{ route('assessor.assessments.period', $period) }}">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Lihat Guru
                        </x-ui.button>
                    </x-slot:actions>
                </x-ui.card>
            @endforeach
        </div>
    @endif

</x-layouts.assessor>
