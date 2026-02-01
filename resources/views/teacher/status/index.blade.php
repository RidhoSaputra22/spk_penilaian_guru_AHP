<x-layouts.teacher>
    <x-slot:title>Status Penilaian</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Status Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Status Penilaian</h1>
                <p class="text-base-content/70 mt-1">Lihat status penilaian kinerja Anda</p>
            </div>
        </div>
    </x-slot:header>

    <!-- Filter -->
    <x-ui.card class="mb-6" compact>
        <form action="{{ route('teacher.status.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <x-ui.select
                    name="period_id"
                    label="Filter Periode"
                    :options="$periods->mapWithKeys(fn($p) => [$p->id => $p->name . ' (' . $p->academic_year . ')'])->toArray()"
                    placeholder="Semua Periode"
                    :selected="request('period_id')"
                />
            </div>
            <div class="flex-1">
                <x-ui.select
                    name="status"
                    label="Filter Status"
                    :options="[
                        'pending' => 'Pending',
                        'draft' => 'Draft',
                        'in_progress' => 'Sedang Dinilai',
                        'submitted' => 'Submitted',
                        'finalized' => 'Final',
                    ]"
                    placeholder="Semua Status"
                    :selected="request('status')"
                />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Filter
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('teacher.status.index') }}">
                    Reset
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($assessments->isEmpty())
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Belum Ada Penilaian</h3>
                <p class="text-base-content/60">Anda belum memiliki penilaian yang terdaftar.</p>
            </div>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Form KPI</th>
                            <th>Penilai</th>
                            <th>Status</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $assessment)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $assessment->period->name ?? '-' }}</div>
                                    <div class="text-sm opacity-50">{{ $assessment->period->academic_year ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="text-sm">
                                        {{ $assessment->assignment->formVersion->template->name ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($assessment->assessor->user->name ?? '?', 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <span>{{ $assessment->assessor->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @switch($assessment->status)
                                        @case('pending')
                                            <x-ui.badge type="ghost" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Pending
                                            </x-ui.badge>
                                            @break
                                        @case('draft')
                                        @case('in_progress')
                                            <x-ui.badge type="warning" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Sedang Dinilai
                                            </x-ui.badge>
                                            @break
                                        @case('submitted')
                                            <x-ui.badge type="info" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Submitted
                                            </x-ui.badge>
                                            @break
                                        @case('finalized')
                                            <x-ui.badge type="success" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Final
                                            </x-ui.badge>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    {{ $assessment->updated_at?->format('d M Y') ?? '-' }}
                                    <div class="text-xs opacity-50">
                                        {{ $assessment->updated_at?->format('H:i') ?? '' }}
                                    </div>
                                </td>
                                <td>
                                    <x-ui.button type="ghost" size="sm" href="{{ route('teacher.status.show', $assessment) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </x-ui.button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($assessments->hasPages())
                <div class="mt-4">
                    {{ $assessments->withQueryString()->links() }}
                </div>
            @endif
        </x-ui.card>
    @endif

</x-layouts.teacher>
