<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Periode Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Periode Penilaian</h1>
                <p class="text-base-content/60">Kelola periode penilaian guru (semester/tahun)</p>
            </div>
            <x-ui.button type="primary" href="{{ route('admin.periods.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Periode
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Draft</div>
            <div class="stat-value text-base-content/60">{{ $statusCounts['draft'] ?? 0 }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Open</div>
            <div class="stat-value text-success">{{ $statusCounts['open'] ?? 0 }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Closed</div>
            <div class="stat-value text-warning">{{ $statusCounts['closed'] ?? 0 }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow">
            <div class="stat-title">Archived</div>
            <div class="stat-value text-base-content/40">{{ $statusCounts['archived'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Filters -->
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.periods.index') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <x-ui.input name="search" placeholder="Cari nama periode..." :value="request('search')" />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select name="academic_year" placeholder="Tahun Ajaran" :options="$academicYears ?? []"
                    :value="request('academic_year')" :searchable="false" />
            </div>
            <div class="w-full md:w-48">
                <x-ui.select name="status" placeholder="Semua Status" :options="[
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'archived' => 'Archived'
                    ]" :value="request('status')" :searchable="false" />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.periods.index') }}">
                    Reset
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <!-- Periods Table -->
    <x-ui.card>
        <div class="">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Waktu Penilaian</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods ?? [] as $period)
                    <tr class="hover">
                        <td>
                            <div class="font-bold">{{ $period->name }}</div>
                        </td>
                        <td>{{ $period->academic_year ?? '-' }}</td>
                        <td>
                            @if($period->semester)
                            <x-ui.badge type="ghost" size="sm">
                                {{ ucfirst($period->semester) }}
                            </x-ui.badge>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @php
                            $statusBadge = match($period->status) {
                            'draft' => 'ghost',
                            'open' => 'success',
                            'closed' => 'warning',
                            'archived' => 'neutral',
                            default => 'ghost'
                            };
                            @endphp
                            <x-ui.badge :type="$statusBadge" size="sm">
                                {{ ucfirst($period->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="text-sm">
                            @if($period->scoring_open_at && $period->scoring_close_at)
                            <div>{{ $period->scoring_open_at->format('d M Y') }}</div>
                            <div class="text-base-content/60">s/d {{ $period->scoring_close_at->format('d M Y') }}</div>
                            @else
                            <span class="text-base-content/40">Belum diatur</span>
                            @endif
                        </td>

                        <td class="text-right">
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </label>
                                <ul tabindex="0"
                                    class="dropdown-content  menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                                    <li><a href="{{ route('admin.periods.show', $period) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat Detail
                                        </a></li>
                                    <li><a href="{{ route('admin.periods.edit', $period) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a></li>
                                    <li><a href="{{ route('admin.ahp.index', ['period' => $period->id]) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Atur Bobot AHP
                                        </a></li>
                                    <div class="divider my-1"></div>
                                    @if($period->status === 'draft')
                                    <li>
                                        <form method="POST" action="{{ route('admin.periods.open', $period) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-success w-full text-left flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                                Buka Periode
                                            </button>
                                        </form>
                                    </li>
                                    @elseif($period->status === 'open')
                                    <li>
                                        <form method="POST" action="{{ route('admin.periods.close', $period) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-warning w-full text-left flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                Tutup Periode
                                            </button>
                                        </form>
                                    </li>
                                    @elseif($period->status === 'closed')
                                    <li>
                                        <form method="POST" action="{{ route('admin.periods.archive', $period) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-full text-left flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                                Arsipkan
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    <li><a onclick="document.getElementById('delete-{{ $period->id }}').showModal()"
                                            class="text-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </a></li>
                                </ul>
                            </div>

                            <!-- Delete Modal -->
                            <x-ui.modal id="delete-{{ $period->id }}" title="Hapus Periode">
                                <p>Anda yakin ingin menghapus periode <strong>{{ $period->name }}</strong>?</p>
                                <p class="text-sm text-error mt-2">Semua data penilaian terkait periode ini akan
                                    dihapus!</p>
                                <x-slot:actions>
                                    <form method="dialog">
                                        <button class="btn btn-ghost">Batal</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.periods.destroy', $period) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="error">Hapus</x-ui.button>
                                    </form>
                                </x-slot:actions>
                            </x-ui.modal>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-base-content/60">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p>Belum ada periode penilaian</p>
                            <x-ui.button type="primary" size="sm" href="{{ route('admin.periods.create') }}"
                                class="mt-4">
                                Buat Periode Pertama
                            </x-ui.button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($periods) && $periods->hasPages())
        <div class="mt-4">
            {{ $periods->links() }}
        </div>
        @endif
    </x-ui.card>
</x-layouts.admin>
