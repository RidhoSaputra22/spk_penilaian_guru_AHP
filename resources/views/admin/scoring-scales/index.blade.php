<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li>Skala Penilaian</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Skala Penilaian</h1>
                <p class="text-base-content/60">Kelola skala penilaian untuk penilaian guru</p>
            </div>
            <x-ui.button type="primary" href="{{ route('admin.scoring-scales.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Skala
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Search and Filters -->
    <x-ui.card>
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <x-ui.input type="search" placeholder="Cari skala penilaian..." name="search"
                    value="{{ request('search') }}" />
            </div>
            <div class="flex gap-2">
                <x-ui.button type="outline" :isSubmit="true">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </x-ui.button>
                <x-ui.button type="ghost" href="{{ route('admin.scoring-scales.index') }}">
                    Reset
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <!-- Scoring Scales Table -->
    <x-ui.card>
        <div class="">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Skala</th>
                        <th>Tipe</th>
                        <th>Jumlah Opsi</th>
                        <th>Range</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scoringScales as $key => $scale)
                    <tr>
                        <td>{{ $scoringScales->firstItem() + $key }}</td>
                        <td>
                            <div class="font-semibold">{{ $scale->name }}</div>
                            @if($scale->description)
                            <div class="text-sm text-base-content/60">
                                {{ Str::limit($scale->description, 50) }}
                            </div>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge type="{{ $scale->scale_type === 'numeric' ? 'info' : 'success' }}">
                                {{ $scale->scale_type === 'numeric' ? 'Numerik' : 'Teks' }}
                            </x-ui.badge>
                        </td>
                        <td>
                            <x-ui.badge type="secondary">
                                {{ $scale->options_count }} Opsi
                            </x-ui.badge>
                        </td>
                        <td>
                            @if($scale->scale_type === 'numeric')
                            <span class="text-sm">
                                {{ $scale->min_value }} - {{ $scale->max_value }}
                                @if($scale->step)
                                (step: {{ $scale->step }})
                                @endif
                            </span>
                            @else
                            <span class="text-sm text-base-content/60">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm">
                                {{ $scale->created_at->format('d M Y') }}
                            </div>
                        </td>
                        <td>
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01" />
                                    </svg>
                                </label>
                                <ul tabindex="0"
                                    class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                                    <li><a href="{{ route('admin.scoring-scales.show', $scale) }}">Detail</a></li>
                                    <li><a href="{{ route('admin.scoring-scales.edit', $scale) }}">Edit</a></li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.scoring-scales.destroy', $scale) }}"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus skala ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error w-full text-left">Hapus</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-16 h-16 text-base-content/30" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <div class="text-center">
                                    <p class="font-semibold">Belum ada skala penilaian</p>
                                    <p class="text-sm text-base-content/70">Tambah skala penilaian pertama Anda</p>
                                </div>
                                <x-ui.button type="primary" href="{{ route('admin.scoring-scales.create') }}">
                                    Tambah Skala
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scoringScales->hasPages())
        <div class="mt-6">
            {{ $scoringScales->links() }}
        </div>
        @endif
    </x-ui.card>
</x-layouts.admin>
