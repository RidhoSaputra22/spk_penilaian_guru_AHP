@extends('layouts.admin')

@section('title', 'Daftar Penilai')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Daftar Penilai</h1>
            <p class="text-base-content/70">Kelola data penilai (assessor) guru</p>
        </div>
        <a href="{{ route('admin.users.create') }}?role=assessor" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Penilai
        </a>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.assessors.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="form-control flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="input input-bordered" />
                </div>
                <div class="form-control w-48">
                    <select name="type" class="select select-bordered">
                        <option value="">Semua Tipe</option>
                        <option value="principal" {{ request('type') == 'principal' ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="supervisor" {{ request('type') == 'supervisor' ? 'selected' : '' }}>Pengawas</option>
                        <option value="peer" {{ request('type') == 'peer' ? 'selected' : '' }}>Teman Sejawat</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search', 'type']))
                    <a href="{{ route('admin.assessors.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Stats -->
    @if($activePeriod)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-title">Total Penilai</div>
            <div class="stat-value text-primary">{{ $assessors->total() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div class="stat-title">Total Penugasan</div>
            <div class="stat-value text-info">{{ $assessors->sum('total_assignments') }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Selesai Dinilai</div>
            <div class="stat-value text-success">{{ $assessors->sum('completed_assignments') }}</div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe Penilai</th>
                            @if($activePeriod)
                            <th>Penugasan</th>
                            <th>Progress</th>
                            @endif
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessors as $assessor)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-secondary/10 text-secondary rounded-full w-10">
                                            <span class="text-sm">{{ substr($assessor->user->name ?? '', 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $assessor->user->name ?? '-' }}</div>
                                        <div class="text-sm text-base-content/70">{{ $assessor->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @switch($assessor->assessor_type)
                                    @case('principal')
                                        <span class="badge badge-primary">Kepala Sekolah</span>
                                        @break
                                    @case('supervisor')
                                        <span class="badge badge-secondary">Pengawas</span>
                                        @break
                                    @default
                                        <span class="badge badge-accent">Teman Sejawat</span>
                                @endswitch
                            </td>
                            @if($activePeriod)
                            <td>
                                <div class="text-sm">
                                    <span class="font-medium">{{ $assessor->completed_assignments }}</span>
                                    <span class="text-base-content/50">/ {{ $assessor->total_assignments }}</span>
                                </div>
                            </td>
                            <td>
                                @if($assessor->total_assignments > 0)
                                    @php
                                        $progress = round(($assessor->completed_assignments / $assessor->total_assignments) * 100);
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <progress class="progress progress-success w-20" value="{{ $progress }}" max="100"></progress>
                                        <span class="text-sm">{{ $progress }}%</span>
                                    </div>
                                @else
                                    <span class="text-base-content/50">-</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                @if($assessor->user?->deactivated_at)
                                    <span class="badge badge-error badge-sm">Nonaktif</span>
                                @else
                                    <span class="badge badge-success badge-sm">Aktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                        <li>
                                            <a href="{{ route('admin.users.edit', $assessor->user_id) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit Data
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.assessments.index') }}?assessor={{ $assessor->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Lihat Penugasan
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $activePeriod ? 6 : 4 }}" class="text-center py-8">
                                <div class="text-base-content/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <p>Belum ada data penilai</p>
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
    @if($assessors->hasPages())
    <div class="flex justify-center">
        {{ $assessors->links() }}
    </div>
    @endif
</div>
@endsection
