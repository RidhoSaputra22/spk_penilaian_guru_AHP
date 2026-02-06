<x-layouts.admin>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Penugasan KPI</h1>
                <p class="text-base-content/70 mt-1">Kelola penugasan formulir KPI ke guru</p>
            </div>
            <a href="{{ route('admin.kpi-assignments.create') }}" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tugaskan KPI
            </a>
            <a href="{{ route('admin.kpi-assignments.bulk-create') }}" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Penugasan Massal
            </a>
        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <x-ui.input type="search" placeholder="Cari guru atau formulir..." name="search"
                        value="{{ request('search') }}" />
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="period" :options="$periods" selected="{{ request('period') }}"
                        placeholder="Semua Periode" />
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="status" :options="$statusOptions" selected="{{ request('status') }}"
                        placeholder="Semua Status" />
                </div>
                <div class="flex gap-2">
                    <x-ui.button variant="outline" size="sm" type="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                    </x-ui.button>
                    <a variant="ghost" size="sm" href="{{ route('admin.kpi-assignments.index') }}"
                        class="btn btn-primary btn-sm">
                        Reset
                    </a>
                </div>
            </form>
        </x-ui.card>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.stat title="Total Penugasan" value="{{ $stats['total'] }}" description="Formulir yang ditugaskan"
                icon="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            <x-ui.stat title="Ditugaskan" value="{{ $stats['assigned'] }}" description="Menunggu dikerjakan"
                icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" class="text-warning" />
            <x-ui.stat title="Dikerjakan" value="{{ $stats['in_progress'] }}" description="Sedang dalam progress"
                icon="M13 10V3L4 14h7v7l9-11h-7z" class="text-info" />
            <x-ui.stat title="Selesai" value="{{ $stats['completed'] }}" description="Telah diselesaikan"
                icon="M5 13l4 4L19 7" class="text-success" />
        </div>

        <!-- Assignments Table -->
        <x-ui.card>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guru</th>
                            <th>Formulir KPI</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $key => $assignment)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div
                                            class="bg-primary text-primary-content w-10 rounded-full flex items-center justify-center">
                                            <span
                                                class="text-sm">{{ substr($assignment->teacher->user->name ?? 'U', 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $assignment->teacher->user->name ?? '-' }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ $assignment->teacher->employee_no ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-semibold">
                                        {{ $assignment->assignment->formVersion->template->name ?? '-' }}
                                    </div>
                                    <div class="text-sm text-base-content/70">
                                        v{{ $assignment->assignment->formVersion->version ?? '1.0' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $assignment->period->name ?? '-' }}
                                    </div>
                                    <div class="text-base-content/70">
                                        {{ $assignment->period->academic_year ?? '' }} -
                                        {{ $assignment->period->semester ?? '' }}
                                    </div>
                                </div>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <div class="text-sm">
                                    @if($assignment->finalized_at)
                                    {{ $assignment->finalized_at->format('d M Y') }}
                                    <div class="text-xs text-success">Selesai</div>
                                    @elseif($assignment->submitted_at)
                                    {{ $assignment->submitted_at->format('d M Y') }}
                                    <div class="text-xs text-info">Submitted</div>
                                    @elseif($assignment->started_at)
                                    {{ $assignment->started_at->format('d M Y') }}
                                    <div class="text-xs text-warning">Dimulai</div>
                                    @else
                                    <span class="text-base-content/50">Belum dimulai</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                // Calculate progress based on status
                                $progress = match($status) {
                                'draft' => 0,
                                'in_progress' => 50,
                                'submitted' => 75,
                                'finalized' => 100,
                                'reopened' => 50,
                                default => 0
                                };
                                @endphp
                                <div class="flex items-center gap-2">
                                    <progress class="progress progress-primary w-16" value="{{ $progress }}"
                                        max="100"></progress>
                                    <span class="text-xs">{{ $progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <a href="{{ route('admin.kpi-assignments.show', $assignment->id) }}"
                                        class="btn btn-ghost btn-xs mt-1" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.kpi-assignments.destroy', $assignment->id) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan penugasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-error" title="Batalkan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <div class="text-center">
                                        <p class="font-semibold">Belum ada penugasan KPI</p>
                                        <p class="text-sm text-base-content/70">Tugaskan formulir KPI pertama kepada
                                            guru
                                        </p>
                                    </div>
                                    <a href="{{ route('admin.kpi-assignments.create') }}"
                                        class="btn btn-primary btn-sm">
                                        Tugaskan KPI
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
