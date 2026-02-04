<x-layouts.admin>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Monitoring Penilaian</h1>
                <p class="text-base-content/70">Pantau progress penilaian guru</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.assessments.create', ['period' => $selectedPeriod?->id]) }}"
                    class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Penugasan
                </a>
            </div>
        </div>

        <!-- Period Selection -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.assessments.index') }}" method="GET" class="flex  gap-4">
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">Periode</span>
                        </label>
                        <select name="period" class="select select-bordered" onchange="this.form.submit()">
                            @foreach($periods as $period)
                            <option value="{{ $period->id }}"
                                {{ $selectedPeriod?->id == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control flex-1 min-w-[200px]">
                        <label class="label">
                            <span class="label-text">Cari Guru</span>
                        </label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama guru..."
                            class="input input-bordered" />
                    </div>
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered">
                            <option value="">Semua</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                Proses</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                            </option>
                        </select>
                    </div>
                    <div class="form-control flex-1">
                        <label class="label">
                            <span class="label-text">&nbsp;</span>
                        </label>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedPeriod)
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat bg-base-100 rounded-box shadow-sm">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="stat-title">Total Penilaian</div>
                <div class="stat-value text-primary">{{ $stats['total'] ?? 0 }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-sm">
                <div class="stat-figure text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title">Pending</div>
                <div class="stat-value text-warning">{{ $stats['pending'] ?? 0 }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-sm">
                <div class="stat-figure text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div class="stat-title">Proses</div>
                <div class="stat-value text-info">{{ $stats['in_progress'] ?? 0 }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-sm">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-title">Selesai</div>
                <div class="stat-value text-success">{{ $stats['completed'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Progress Overview -->
        @if(($stats['total'] ?? 0) > 0)
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h3 class="card-title text-base">Progress Keseluruhan</h3>
                @php
                $progress = round((($stats['completed'] ?? 0) / ($stats['total'] ?? 1)) * 100);
                @endphp
                <div class="flex items-center gap-4">
                    <progress class="progress progress-success flex-1 h-4" value="{{ $progress }}" max="100"></progress>
                    <span class="text-lg font-semibold">{{ $progress }}%</span>
                </div>
                <p class="text-sm text-base-content/70">
                    {{ $stats['completed'] ?? 0 }} dari {{ $stats['total'] ?? 0 }} penilaian telah selesai
                </p>
            </div>
        </div>
        @endif

        <!-- Assessment List -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th>Penilai</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assessments as $assessment)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-primary/10 text-primary rounded-full w-10 flex items-center justify-center">
                                                <span
                                                    class="text-sm">{{ substr($assessment->teacher->user->name ?? '', 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $assessment->teacher->user->name ?? '-' }}</div>
                                            <div class="text-sm text-base-content/70">
                                                {{ $assessment->teacher->nip ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-secondary/10 text-secondary rounded-full w-8 flex items-center justify-center">
                                                <span
                                                    class="text-xs">{{ substr($assessment->assessor->user->name ?? '', 0, 2) }}</span>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $assessment->assessor->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @switch($assessment->status)
                                    @case('submitted')
                                    @case('finalized')
                                    <span class="badge badge-success">Selesai</span>
                                    @break
                                    @case('in_progress')
                                    <span class="badge badge-info">Proses</span>
                                    @break
                                    @default
                                    <span class="badge badge-warning">Pending</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="text-sm text-base-content/70">
                                        @if($assessment->submitted_at)
                                        Selesai {{ $assessment->submitted_at->format('d M Y') }}
                                        @elseif($assessment->started_at)
                                        Mulai {{ $assessment->started_at->format('d M Y') }}
                                        @else
                                        Belum dimulai
                                        @endif
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.assessments.show', $assessment) }}"
                                        class="btn btn-ghost btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-8">
                                    <div class="text-base-content/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p>Belum ada data penilaian</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($assessments->hasPages())
        <div class="flex justify-end">
            {{ $assessments->links() }}
        </div>
        @endif
        @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-base-content/30 mb-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-medium">Tidak Ada Periode</h3>
                <p class="text-base-content/70">Silakan buat periode penilaian terlebih dahulu</p>
                <a href="{{ route('admin.periods.create') }}" class="btn btn-primary mt-4">
                    Buat Periode
                </a>
            </div>
        </div>
        @endif
    </div>
</x-layouts.admin>