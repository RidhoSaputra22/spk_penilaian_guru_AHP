<x-layouts.admin>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Detail Penugasan KPI</h1>
                <p class="text-base-content/70 mt-1">Informasi lengkap penugasan formulir KPI</p>
            </div>
            <a href="{{ route('admin.kpi-assignments.index') }}" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>

        <!-- Assignment Info -->
        <x-ui.card>
            <div class="space-y-6">
                <!-- Status Badge -->
                <div>
                    @php
                    $status = $assignment->status ?? 'draft';
                    $statusConfig = [
                    'draft' => ['badge' => 'warning', 'text' => 'Ditugaskan'],
                    'in_progress' => ['badge' => 'info', 'text' => 'Dikerjakan'],
                    'submitted' => ['badge' => 'primary', 'text' => 'Submitted'],
                    'finalized' => ['badge' => 'success', 'text' => 'Selesai'],
                    'reopened' => ['badge' => 'error', 'text' => 'Dibuka Kembali'],
                    ];
                    @endphp
                    <x-ui.badge variant="{{ $statusConfig[$status]['badge'] ?? 'default' }}">
                        {{ $statusConfig[$status]['text'] ?? ucfirst($status) }}
                    </x-ui.badge>
                </div>

                <!-- Main Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Guru -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Guru</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-primary text-primary-content w-12 rounded-full flex items-center justify-center">
                                    <span
                                        class="text-sm">{{ substr($assignment->teacher->user->name ?? 'U', 0, 2) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-semibold">{{ $assignment->teacher->user->name ?? '-' }}</div>
                                <div class="text-sm text-base-content/70">{{ $assignment->teacher->employee_no ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessor -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Penilai (Assessor)</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-secondary text-secondary-content w-12 rounded-full flex items-center justify-center">
                                    <span
                                        class="text-sm">{{ substr($assignment->assessor->user->name ?? 'U', 0, 2) }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-semibold">{{ $assignment->assessor->user->name ?? '-' }}</div>
                                <div class="text-sm text-base-content/70">Assessor</div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulir KPI -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Formulir KPI</span>
                        </label>
                        <div>
                            <div class="font-semibold">{{ $assignment->assignment->formVersion->template->name ?? '-' }}
                            </div>
                            <div class="text-sm text-base-content/70">
                                v{{ $assignment->assignment->formVersion->version ?? '1.0' }}</div>
                        </div>
                    </div>

                    <!-- Periode -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Periode Penilaian</span>
                        </label>
                        <div>
                            <div class="font-semibold">{{ $assignment->period->name ?? '-' }}</div>
                            <div class="text-sm text-base-content/70">
                                {{ $assignment->period->academic_year ?? '' }} -
                                {{ $assignment->period->semester ?? '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Timeline</span>
                    </label>
                    <div class="space-y-2">
                        @if($assignment->started_at)
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Dimulai:</span>
                            <span>{{ $assignment->started_at->format('d M Y, H:i') }}</span>
                        </div>
                        @endif

                        @if($assignment->submitted_at)
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">Submitted:</span>
                            <span>{{ $assignment->submitted_at->format('d M Y, H:i') }}</span>
                        </div>
                        @endif

                        @if($assignment->finalized_at)
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-medium">Finalized:</span>
                            <span>{{ $assignment->finalized_at->format('d M Y, H:i') }}</span>
                        </div>
                        @endif

                        @if(!$assignment->started_at)
                        <div class="text-sm text-base-content/50">
                            Belum dimulai
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Progress -->
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Progress Penilaian</span>
                    </label>
                    @php
                    $progress = match($status) {
                    'draft' => 0,
                    'in_progress' => 50,
                    'submitted' => 75,
                    'finalized' => 100,
                    'reopened' => 50,
                    default => 0
                    };
                    @endphp
                    <div class="flex items-center gap-3">
                        <progress class="progress progress-primary flex-1" value="{{ $progress }}" max="100"></progress>
                        <span class="font-semibold">{{ $progress }}%</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <form method="POST" action="{{ route('admin.kpi-assignments.destroy', $assignment->id) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error text-white">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Batalkan Penugasan
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>