<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.periods.index') }}">Periode Penilaian</a></li>
        <li>{{ $period->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $period->name }}</h1>
                <p class="text-base-content/60">Detail periode penilaian</p>
            </div>
            <div class="flex gap-2">
                <x-ui.button type="ghost" href="{{ route('admin.periods.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </x-ui.button>
                <x-ui.button type="primary" href="{{ route('admin.periods.edit', $period) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-ui.button>
            </div>
        </div>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <x-ui.card title="Informasi Periode">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-medium text-sm">Nama Periode</label>
                        <p class="text-base-content/80 mt-1">{{ $period->name }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-sm">Status</label>
                        <div class="mt-1">
                            @php
                                $statusBadge = match($period->status) {
                                    'draft' => 'ghost',
                                    'open' => 'success',
                                    'closed' => 'warning',
                                    'archived' => 'neutral',
                                    default => 'ghost'
                                };
                            @endphp
                            <x-ui.badge :type="$statusBadge">{{ ucfirst($period->status) }}</x-ui.badge>
                        </div>
                    </div>
                    <div>
                        <label class="font-medium text-sm">Tahun Ajaran</label>
                        <p class="text-base-content/80 mt-1">{{ $period->academic_year ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-sm">Semester</label>
                        <p class="text-base-content/80 mt-1">
                            {{ $period->semester ? ucfirst($period->semester) : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="font-medium text-sm">Tanggal Mulai</label>
                        <p class="text-base-content/80 mt-1">
                            {{ $period->scoring_open_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="font-medium text-sm">Tanggal Selesai</label>
                        <p class="text-base-content/80 mt-1">
                            {{ $period->scoring_close_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>
                </div>

                @if($period->meta['description'] ?? null)
                <div class="mt-6 pt-6 border-t border-base-200">
                    <label class="font-medium text-sm">Deskripsi</label>
                    <p class="text-base-content/80 mt-1">{{ $period->meta['description'] }}</p>
                </div>
                @endif
            </x-ui.card>

            <!-- AHP Configuration -->
            <x-ui.card title="Konfigurasi AHP">
                @if($period->ahpModel)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="font-medium text-sm">Set Kriteria</label>
                            <p class="text-base-content/80 mt-1">{{ $period->ahpModel->criteriaSet?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="font-medium text-sm">Status AHP</label>
                            <div class="mt-1">
                                @php
                                    $ahpStatusBadge = match($period->ahpModel->status) {
                                        'draft' => 'ghost',
                                        'in_progress' => 'warning',
                                        'finalized' => 'success',
                                        default => 'ghost'
                                    };
                                @endphp
                                <x-ui.badge :type="$ahpStatusBadge">{{ ucfirst($period->ahpModel->status) }}</x-ui.badge>
                            </div>
                        </div>
                        @if($period->ahpModel->consistency_ratio)
                        <div>
                            <label class="font-medium text-sm">Consistency Ratio</label>
                            <p class="text-base-content/80 mt-1">{{ number_format($period->ahpModel->consistency_ratio, 4) }}</p>
                        </div>
                        @endif
                        @if($period->ahpModel->finalized_at)
                        <div>
                            <label class="font-medium text-sm">Tanggal Finalisasi</label>
                            <p class="text-base-content/80 mt-1">{{ $period->ahpModel->finalized_at->format('d M Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="mt-4">
                        <x-ui.button type="primary" size="sm" href="{{ route('admin.ahp.index', ['period' => $period->id]) }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Kelola Bobot AHP
                        </x-ui.button>
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/60">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p>Belum ada konfigurasi AHP</p>
                        <x-ui.button type="primary" size="sm" href="{{ route('admin.periods.edit', $period) }}" class="mt-4">
                            Atur Set Kriteria
                        </x-ui.button>
                    </div>
                @endif
            </x-ui.card>

            <!-- KPI Form Assignment -->
            <x-ui.card title="Form KPI">
                @if($period->assignments->isNotEmpty())
                    @foreach($period->assignments as $assignment)
                    <div class="border border-base-200 rounded-lg p-4 mb-4 last:mb-0">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="font-medium text-sm">Form KPI</label>
                                <p class="text-base-content/80 mt-1">
                                    {{ $assignment->formVersion?->template?->name ?? 'Unknown Form' }}
                                    <span class="text-xs text-base-content/60">v{{ $assignment->formVersion?->version ?? '?' }}</span>
                                </p>
                            </div>
                            <div>
                                <label class="font-medium text-sm">Status</label>
                                <div class="mt-1">
                                    @php
                                        $assignmentStatusBadge = match($assignment->status) {
                                            'draft' => 'ghost',
                                            'assigned' => 'warning',
                                            'locked' => 'success',
                                            default => 'ghost'
                                        };
                                    @endphp
                                    <x-ui.badge :type="$assignmentStatusBadge" size="sm">{{ ucfirst($assignment->status) }}</x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <label class="font-medium text-sm">Ditugaskan</label>
                                <p class="text-base-content/80 mt-1">{{ $assignment->assigned_at?->format('d M Y') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-8 text-base-content/60">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>Belum ada form KPI yang ditugaskan</p>
                        <x-ui.button type="primary" size="sm" href="{{ route('admin.periods.edit', $period) }}" class="mt-4">
                            Atur Form KPI
                        </x-ui.button>
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Assessment Stats -->
            @if($assessmentStats)
            <x-ui.card title="Statistik Penilaian">
                <div class="space-y-3">
                    @foreach($assessmentStats as $status => $count)
                        @php
                            $statusInfo = match($status) {
                                'draft' => ['text' => 'Draft', 'class' => 'text-base-content/60'],
                                'in_progress' => ['text' => 'Dalam Proses', 'class' => 'text-warning'],
                                'completed' => ['text' => 'Selesai', 'class' => 'text-success'],
                                default => ['text' => ucfirst($status), 'class' => 'text-base-content/60']
                            };
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-sm {{ $statusInfo['class'] }}">{{ $statusInfo['text'] }}</span>
                            <span class="font-bold {{ $statusInfo['class'] }}">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
            @endif

            <!-- Quick Actions -->
            <x-ui.card title="Aksi Cepat">
                <div class="space-y-2">
                    @if($period->status === 'draft')
                        <form method="POST" action="{{ route('admin.periods.open', $period) }}" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                Buka Periode
                            </button>
                        </form>
                    @elseif($period->status === 'open')
                        <form method="POST" action="{{ route('admin.periods.close', $period) }}" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning btn-sm w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Tutup Periode
                            </button>
                        </form>
                    @elseif($period->status === 'closed')
                        <form method="POST" action="{{ route('admin.periods.archive', $period) }}" class="w-full">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-neutral btn-sm w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Arsipkan
                            </button>
                        </form>
                    @endif

                    <x-ui.button type="primary" size="sm" href="{{ route('admin.periods.edit', $period) }}" class="w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Periode
                    </x-ui.button>
                </div>
            </x-ui.card>

            <!-- Metadata -->
            <x-ui.card title="Informasi">
                <div class="space-y-3 text-sm">
                    <div>
                        <label class="font-medium">Dibuat</label>
                        <p class="text-base-content/60">{{ $period->created_at->format('d M Y H:i') }}</p>
                    </div>
                    @if($period->updated_at && $period->updated_at != $period->created_at)
                    <div>
                        <label class="font-medium">Terakhir Diubah</label>
                        <p class="text-base-content/60">{{ $period->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    @endif
                    <div>
                        <label class="font-medium">ID</label>
                        <p class="text-base-content/60 font-mono text-xs">{{ $period->id }}</p>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
