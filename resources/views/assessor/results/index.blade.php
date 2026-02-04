<x-layouts.assessor>
    <x-slot:title>Hasil Penilaian</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Hasil</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">Lihat hasil penilaian yang telah Anda submit</p>
            </div>
        </div>
    </x-slot:header>

    <!-- Filter -->
    <x-ui.card class="mb-6" compact>
        <form action="{{ route('assessor.results.index') }}" method="GET"
            class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1">
                <label class="label">
                    <span class="label-text">Filter Periode</span>
                </label>
                <select name="period_id" class="select select-bordered w-full" onchange="this.form.submit()">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $period)
                    <option value="{{ $period->id }}" {{ request('period_id') == $period->id ? 'selected' : '' }}>
                        {{ $period->name }} ({{ $period->academic_year }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-ui.button type="ghost" href="{{ route('assessor.results.index') }}" class="pri">
                    Reset Filter
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if($assessments->isEmpty())
    <x-ui.card>
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-semibold mb-2">Belum Ada Hasil</h3>
            <p class="text-base-content/60">Anda belum memiliki penilaian yang sudah disubmit.</p>
            <x-ui.button type="primary" href="{{ route('assessor.assessments.index') }}" class="mt-4">
                Mulai Penilaian
            </x-ui.button>
        </div>
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>Periode</th>
                        <th>Form KPI</th>
                        <th>Tanggal Submit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assessments as $assessment)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div
                                        class="bg-neutral text-neutral-content rounded-full w-10 flex items-center justify-center">
                                        <span>{{ substr($assessment->teacher->user->name ?? '?', 0, 1) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-bold">{{ $assessment->teacher->user->name ?? '-' }}</div>
                                    <div class="text-sm opacity-50">{{ $assessment->teacher->employee_no ?? '' }}</div>
                                </div>
                            </div>
                        </td>
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
                            {{ $assessment->submitted_at?->format('d M Y') ?? '-' }}
                            <div class="text-xs opacity-50">
                                {{ $assessment->submitted_at?->format('H:i') ?? '' }}
                            </div>
                        </td>
                        <td>
                            @if($assessment->status === 'finalized')
                            <x-ui.badge type="success" size="sm">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Final
                            </x-ui.badge>
                            @else
                            <x-ui.badge type="info" size="sm">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Submitted
                            </x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <x-ui.button type="ghost" size="sm"
                                href="{{ route('assessor.results.show', $assessment) }}">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
            {{ $assessments->links() }}
        </div>
        @endif
    </x-ui.card>
    @endif

</x-layouts.assessor>
