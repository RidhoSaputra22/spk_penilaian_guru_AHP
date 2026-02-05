<x-layouts.admin>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.assessments.index') }}" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <div>
                <h1 class="text-2xl font-bold">Detail Penilaian</h1>
                <p class="text-base-content/60">Informasi lengkap penilaian guru</p>
            </div>
        </div>

        <!-- Assessment Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Teacher Info -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="card-title">Informasi Guru</h3>
                                <div class="space-y-2 mt-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-primary/10 text-primary rounded-full w-12 flex items-center justify-center">
                                                <span
                                                    class="text-sm">{{ substr($assessment->teacher->user->name ?? '', 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-base">
                                                {{ $assessment->teacher->user->name ?? '-' }}</div>
                                            <div class="text-sm text-base-content/60">NIP:
                                                {{ $assessment->teacher->nip ?? '-' }}</div>
                                            <div class="text-sm text-base-content/60">
                                                {{ $assessment->teacher->user->email ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="badge badge-{{ $assessment->status === 'submitted' ? 'success' : ($assessment->status === 'in_progress' ? 'warning' : 'ghost') }}">
                                @if($assessment->status === 'pending')
                                Belum dimulai
                                @elseif($assessment->status === 'in_progress')
                                Sedang dinilai
                                @elseif($assessment->status === 'submitted')
                                Selesai
                                @else
                                {{ ucfirst($assessment->status) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessor Info -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Informasi Penilai</h3>
                        <div class="space-y-2 mt-4">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div
                                        class="bg-secondary/10 text-secondary rounded-full w-12 flex items-center justify-center">
                                        <span
                                            class="text-sm">{{ substr($assessment->assessor->user->name ?? '', 0, 2) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-base">{{ $assessment->assessor->user->name ?? '-' }}
                                    </div>
                                    <div class="text-sm text-base-content/60">
                                        {{ $assessment->assessor->user->email ?? '-' }}</div>
                                    <div class="text-sm text-base-content/60">
                                        Tipe: {{ ucfirst($assessment->assessor->assessor_type ?? 'internal') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Progress -->
                @if($assessment->itemValues && $assessment->itemValues->count() > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">Progress Penilaian</h3>
                        <div class="space-y-4 mt-4">
                            @php
                            $totalItems = $assessment->assignment->formVersion->template->sections->sum('items_count')
                            ?? 0;
                            $completedItems = $assessment->itemValues->count();
                            $progressPercentage = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
                            @endphp

                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Progress</span>
                                    <span>{{ $completedItems }}/{{ $totalItems }} item</span>
                                </div>
                                <div class="progress progress-primary bg-base-200">
                                    <div class="progress-bar" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            @if($assessment->submitted_at)
                            <div class="alert alert-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6"
                                    fill="none" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Penilaian telah diselesaikan pada
                                    {{ $assessment->submitted_at->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Period Info -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-base">Periode Penilaian</h3>
                        <div class="space-y-2 mt-4">
                            <div class="text-sm">
                                <span class="text-base-content/60">Periode:</span>
                                <div class="font-medium">{{ $assessment->period->name ?? '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <span class="text-base-content/60">Mulai Penilaian:</span>
                                <div class="font-medium">
                                    {{ $assessment->period->scoring_open_at ? \Carbon\Carbon::parse($assessment->period->scoring_open_at)->format('d M Y') : '-' }}
                                </div>
                            </div>
                            <div class="text-sm">
                                <span class="text-base-content/60">Selesai:</span>
                                <div class="font-medium">
                                    {{ $assessment->period->scoring_close_at ? \Carbon\Carbon::parse($assessment->period->scoring_close_at)->format('d M Y') : '-' }}
                                </div>
                            </div>
                            <div class="divider divider-start text-xs">Status Periode</div>
                            <div
                                class="badge badge-{{ $assessment->period->status === 'open' ? 'success' : 'ghost' }} badge-sm">
                                {{ ucfirst($assessment->period->status ?? 'draft') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Info -->
                @if($assessment->assignment && $assessment->assignment->formVersion)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-base">Formulir Penilaian</h3>
                        <div class="space-y-2 mt-4">
                            <div class="text-sm">
                                <span class="text-base-content/60">Template:</span>
                                <div class="font-medium">
                                    {{ $assessment->assignment->formVersion->template->name ?? '-' }}</div>
                            </div>
                            <div class="text-sm">
                                <span class="text-base-content/60">Versi:</span>
                                <div class="font-medium">
                                    v{{ $assessment->assignment->formVersion->version_number ?? '1' }}</div>
                            </div>
                            <div class="text-sm">
                                <span class="text-base-content/60">Status:</span>
                                <div class="badge badge-success badge-sm">
                                    {{ ucfirst($assessment->assignment->formVersion->status ?? 'published') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Timeline -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-base">Timeline</h3>
                        <div class="space-y-3 mt-4">
                            @if($assessment->statusLogs && $assessment->statusLogs->count() > 0)
                            @foreach($assessment->statusLogs->take(5) as $log)
                            <div class="flex gap-3">
                                <div class="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                                <div class="text-sm">
                                    <div class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</div>
                                    <div class="text-base-content/60">
                                        {{ $log->created_at->format('d M Y H:i') }}
                                        @if($log->user)
                                        oleh {{ $log->user->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="text-sm text-base-content/60">Belum ada aktivitas</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>