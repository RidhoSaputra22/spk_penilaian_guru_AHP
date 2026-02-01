@extends('layouts.admin')

@section('title', 'Daftar Guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Daftar Guru</h1>
            <p class="text-base-content/70">Kelola data guru yang akan dinilai</p>
        </div>
        <a href="{{ route('admin.users.create') }}?role=teacher" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Guru
        </a>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="form-control flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIP, NUPTK..." class="input input-bordered" />
                </div>
                <div class="form-control w-48">
                    <select name="group" class="select select-bordered">
                        <option value="">Semua Kelompok</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-40">
                    <select name="status" class="select select-bordered">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search', 'group', 'status']))
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-ghost">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="stat-title">Total Guru</div>
            <div class="stat-value text-primary">{{ $teachers->total() }}</div>
        </div>

        @if($activePeriod)
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Sudah Dinilai</div>
            <div class="stat-value text-success">{{ $teachers->where('current_assessment_status', 'completed')->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-warning">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Sedang Dinilai</div>
            <div class="stat-value text-warning">{{ $teachers->where('current_assessment_status', 'in_progress')->count() }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-sm">
            <div class="stat-figure text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-title">Belum Ditugaskan</div>
            <div class="stat-value">{{ $teachers->where('current_assessment_status', 'not_assigned')->count() }}</div>
        </div>
        @endif
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIP / NUPTK</th>
                            <th>Kelompok</th>
                            <th>Jabatan</th>
                            @if($activePeriod)
                            <th>Status Penilaian</th>
                            @endif
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary/10 text-primary rounded-full w-10">
                                            <span class="text-sm">{{ substr($teacher->user->name ?? '', 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $teacher->user->name ?? '-' }}</div>
                                        <div class="text-sm text-base-content/70">{{ $teacher->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div>NIP: {{ $teacher->nip ?? '-' }}</div>
                                    <div class="text-base-content/70">NUPTK: {{ $teacher->nuptk ?? '-' }}</div>
                                </div>
                            </td>
                            <td>
                                @if($teacher->teacherGroup)
                                    <span class="badge badge-outline">{{ $teacher->teacherGroup->name }}</span>
                                @else
                                    <span class="text-base-content/50">-</span>
                                @endif
                            </td>
                            <td>{{ $teacher->position ?? '-' }}</td>
                            @if($activePeriod)
                            <td>
                                @switch($teacher->current_assessment_status)
                                    @case('completed')
                                        <span class="badge badge-success gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Selesai
                                        </span>
                                        @break
                                    @case('in_progress')
                                        <span class="badge badge-warning gap-1">
                                            <span class="loading loading-spinner loading-xs"></span>
                                            Proses
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-info">Menunggu</span>
                                        @break
                                    @default
                                        <span class="badge badge-ghost">Belum Ditugaskan</span>
                                @endswitch
                            </td>
                            @endif
                            <td>
                                @if($teacher->user?->deactivated_at)
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
                                            <a href="{{ route('admin.users.edit', $teacher->user_id) }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit Data
                                            </a>
                                        </li>
                                        @if($activePeriod && $teacher->current_assessment_status == 'not_assigned')
                                        <li>
                                            <a href="{{ route('admin.assessments.index') }}?assign={{ $teacher->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Tugaskan Penilai
                                            </a>
                                        </li>
                                        @endif
                                        @if($teacher->current_assessment_status == 'completed')
                                        <li>
                                            <a href="{{ route('admin.results.index') }}?teacher={{ $teacher->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                Lihat Hasil
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $activePeriod ? 7 : 6 }}" class="text-center py-8">
                                <div class="text-base-content/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p>Belum ada data guru</p>
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
    @if($teachers->hasPages())
    <div class="flex justify-center">
        {{ $teachers->links() }}
    </div>
    @endif
</div>
@endsection
