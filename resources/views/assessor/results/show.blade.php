<x-layouts.assessor>
    <x-slot:title>Detail Hasil - {{ $assessment->teacher->user->name ?? 'Guru' }}</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('assessor.results.index') }}">Hasil</a></li>
        <li>{{ $assessment->teacher->user->name ?? 'Detail' }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Hasil Penilaian</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $assessment->period->name ?? '' }} - {{ $assessment->period->academic_year ?? '' }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('assessor.results.index') }}">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </x-ui.button>
                <x-ui.button type="outline" onclick="window.print()">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <!-- Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Teacher Info -->
        <x-ui.card title="Informasi Guru">
            <div class="flex items-start gap-4">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-16 flex items-center justify-center">
                        <span class="text-2xl">{{ substr($assessment->teacher->user->name ?? '?', 0, 1) }}</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold">{{ $assessment->teacher->user->name ?? '-' }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 text-sm">
                        <div>
                            <span class="text-base-content/60">NIP/NIK:</span>
                            <span class="font-medium ml-1">{{ $assessment->teacher->employee_no ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60">Mata Pelajaran:</span>
                            <span class="font-medium ml-1">{{ $assessment->teacher->subject ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60">Email:</span>
                            <span class="font-medium ml-1">{{ $assessment->teacher->user->email ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-base-content/60">Jabatan:</span>
                            <span class="font-medium ml-1">{{ $assessment->teacher->position ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Assessment Info -->
        <x-ui.card title="Informasi Penilaian">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-base-content/60">Status:</span>
                    @if($assessment->status === 'finalized')
                    <x-ui.badge type="success">Final</x-ui.badge>
                    @else
                    <x-ui.badge type="info">Submitted</x-ui.badge>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Form KPI:</span>
                    <span class="font-medium">{{ $assessment->assignment->formVersion->template->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Tanggal Mulai:</span>
                    <span>{{ $assessment->started_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/60">Tanggal Submit:</span>
                    <span>{{ $assessment->submitted_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
                @if($assessment->finalized_at)
                <div class="flex justify-between">
                    <span class="text-base-content/60">Tanggal Final:</span>
                    <span>{{ $assessment->finalized_at->format('d M Y H:i') }}</span>
                </div>
                @endif
            </div>
        </x-ui.card>
    </div>

    <!-- Assessment Details -->
    @php
    $formVersion = $assessment->assignment->formVersion;
    @endphp

    <div class="space-y-6">
        @foreach($formVersion->sections as $sectionIndex => $section)
        <x-ui.card>
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold">
                    {{ $sectionIndex + 1 }}
                </div>
                <div>
                    <h3 class="text-lg font-bold">{{ $section->title }}</h3>
                    @if($section->description)
                    <p class="text-sm text-base-content/60">{{ $section->description }}</p>
                    @endif
                </div>
            </div>

            <div class="divider my-2"></div>

            @if($section->items->isEmpty())
            <p class="text-base-content/50 text-center py-4">Tidak ada indikator di section ini</p>
            @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Indikator</th>
                            <th class="w-32 text-center">Nilai</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section->items as $itemIndex => $item)
                        @php
                        $value = $valuesMap[$item->id] ?? null;
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $sectionIndex + 1 }}.{{ $itemIndex + 1 }}</td>
                            <td>
                                <div class="font-medium">{{ $item->label }}</div>
                                @if($item->help_text)
                                <div class="text-xs text-base-content/60">{{ $item->help_text }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($value)
                                @switch($item->field_type)
                                @case('checkbox')
                                @if($value->value_bool)
                                <x-ui.badge type="success" size="sm">Ya</x-ui.badge>
                                @else
                                <x-ui.badge type="ghost" size="sm">Tidak</x-ui.badge>
                                @endif
                                @break
                                @case('textarea')
                                <span class="text-sm">{{ Str::limit($value->value_string, 50) }}</span>
                                @break
                                @default
                                @if($item->scale && $value->value_number !== null)
                                @php
                                $scaleOption = $item->scale->options->firstWhere('numeric_value', $value->value_number);
                                @endphp
                                <div class="flex flex-col items-center">
                                    <span class="text-xl font-bold text-primary">{{ number_format($value->value_number, 0, ',', '.') }}</span>
                                    @if($scaleOption)
                                    <span class="text-xs text-base-content/60">{{ $scaleOption->label }}</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-xl font-bold text-primary">
                                    {{ $value->value_number ? number_format($value->value_number, 0, ',', '.') : ($value->value_string ?? '-') }}
                                </span>
                                @endif
                                @endswitch
                                @else
                                <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                            <td>
                                @if($value && $value->notes)
                                <div class="text-sm bg-base-200 rounded p-2">
                                    {{ $value->notes }}
                                </div>
                                @else
                                <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-ui.card>
        @endforeach
    </div>

    <!-- Status History -->
    @if($assessment->statusLogs->isNotEmpty())
    <x-ui.card title="Riwayat Status" class="mt-6">
        <ul class="timeline timeline-vertical">
            @foreach($assessment->statusLogs->sortByDesc('created_at') as $log)
            <li>
                <div class="timeline-start text-sm text-base-content/60">
                    {{ $log->created_at->format('d M Y H:i') }}
                </div>
                <div class="timeline-middle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-5 h-5 text-primary">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="timeline-end timeline-box">
                    <div class="flex items-center gap-2">
                        <x-ui.badge type="ghost" size="sm">{{ ucfirst($log->from_status) }}</x-ui.badge>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <x-ui.badge type="primary" size="sm">{{ ucfirst($log->to_status) }}</x-ui.badge>
                    </div>
                    @if($log->notes)
                    <p class="text-sm mt-1">{{ $log->notes }}</p>
                    @endif
                    @if($log->user)
                    <p class="text-xs text-base-content/60 mt-1">Oleh: {{ $log->user->name }}</p>
                    @endif
                </div>
                <hr />
            </li>
            @endforeach
        </ul>
    </x-ui.card>
    @endif

    <!-- Print Styles -->
    <style>
    @media print {

        .btn,
        .dropdown,
        aside,
        header,
        footer,
        .no-print {
            display: none !important;
        }

        .card {
            break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
    </style>

</x-layouts.assessor>
