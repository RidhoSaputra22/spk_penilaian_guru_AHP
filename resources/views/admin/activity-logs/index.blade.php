<x-layouts.admin>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Log Aktivitas</h1>
                <p class="text-base-content/70">Riwayat aktivitas pengguna dalam sistem</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="form-control flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari aktivitas atau nama..." class="input input-bordered" />
                    </div>
                    <div class="form-control w-48">
                        <select name="action" class="select select-bordered">
                            <option value="">Semua Aksi</option>
                            @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $action)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control w-40">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="input input-bordered" placeholder="Dari" />
                    </div>
                    <div class="form-control w-40">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input input-bordered"
                            placeholder="Sampai" />
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-ghost">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                @forelse($logs as $log)
                <div
                    class="flex gap-4 pb-6 {{ !$loop->last ? 'border-b border-base-200' : '' }} {{ !$loop->first ? 'pt-6' : '' }}">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="avatar placeholder">
                            <div class="bg-primary/10 text-primary rounded-full w-10">
                                <span class="text-sm">{{ substr($log->user->name ?? 'S', 0, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="font-medium">{{ $log->user->name ?? 'System' }}</span>
                            <span class="badge badge-ghost badge-sm">
                                @switch($log->action)
                                @case('create_user')
                                @case('create_period')
                                @case('create_criteria_set')
                                @case('create_criteria_node')
                                @case('create_ahp_model')
                                @case('create_kpi_template')
                                @case('create_kpi_version')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-success" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                @break
                                @case('update_user')
                                @case('update_period')
                                @case('update_criteria_set')
                                @case('update_criteria_node')
                                @case('update_kpi_form')
                                @case('save_ahp_comparisons')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-info" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                @break
                                @case('delete_user')
                                @case('delete_period')
                                @case('delete_criteria_set')
                                @case('delete_criteria_node')
                                @case('delete_kpi_template')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-error" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                @break
                                @default
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                @endswitch
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </div>
                        <p class="text-base-content/70">{{ $log->description }}</p>
                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-base-content/50">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $log->created_at->format('d M Y, H:i') }}
                            </span>
                            @if($log->ip_address)
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                {{ $log->ip_address }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Time -->
                    <div class="flex-shrink-0 text-sm text-base-content/50">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-base-content/30 mb-4"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium">Tidak Ada Log</h3>
                    <p class="text-base-content/70">Belum ada aktivitas yang tercatat</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="flex justify-center">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</x-layouts.admin>
