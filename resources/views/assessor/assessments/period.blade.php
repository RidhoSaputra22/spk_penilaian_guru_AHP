<x-layouts.assessor>
    <x-slot:title>{{ $period->name }} - Daftar Guru</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('assessor.assessments.index') }}">Penilaian</a></li>
        <li>{{ $period->name }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $period->name }}</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $period->academic_year }} - Semester {{ $period->semester }}
                    @if($period->status === 'active')
                        <x-ui.badge type="success" size="sm" class="ml-2">Aktif</x-ui.badge>
                    @else
                        <x-ui.badge type="ghost" size="sm" class="ml-2">{{ ucfirst($period->status) }}</x-ui.badge>
                    @endif
                </p>
            </div>
            <x-ui.button type="ghost" href="{{ route('assessor.assessments.index') }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    @if($period->status !== 'active')
        <x-ui.alert type="warning" class="mb-4">
            Periode ini sudah ditutup. Anda hanya dapat melihat hasil penilaian.
        </x-ui.alert>
    @endif

    @if($teachers->isEmpty())
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Tidak Ada Guru</h3>
                <p class="text-base-content/60">Anda belum ditugaskan untuk menilai guru di periode ini.</p>
            </div>
        </x-ui.card>
    @else
        <x-ui.card title="Daftar Guru yang Dinilai">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Guru</th>
                            <th>NIP/NIK</th>
                            <th>Mata Pelajaran</th>
                            <th>Status Penilaian</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $index => $teacher)
                            @php
                                $assessment = $existingAssessments[$teacher->id] ?? null;
                                $status = $assessment?->status ?? 'pending';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                <span>{{ substr($teacher->user->name ?? '?', 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $teacher->user->name ?? '-' }}</div>
                                            <div class="text-sm opacity-50">{{ $teacher->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $teacher->employee_no ?? '-' }}</td>
                                <td>{{ $teacher->subject ?? '-' }}</td>
                                <td>
                                    @switch($status)
                                        @case('draft')
                                            <x-ui.badge type="warning" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Draft
                                            </x-ui.badge>
                                            @break
                                        @case('submitted')
                                            <x-ui.badge type="info" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Submitted
                                            </x-ui.badge>
                                            @break
                                        @case('finalized')
                                            <x-ui.badge type="success" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Final
                                            </x-ui.badge>
                                            @break
                                        @default
                                            <x-ui.badge type="ghost" size="sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Belum Dinilai
                                            </x-ui.badge>
                                    @endswitch
                                </td>
                                <td>
                                    @if(in_array($status, ['submitted', 'finalized']))
                                        <x-ui.button type="ghost" size="sm" href="{{ route('assessor.results.show', $assessment) }}">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat Hasil
                                        </x-ui.button>
                                    @elseif($period->status === 'active')
                                        <x-ui.button type="primary" size="sm" href="{{ route('assessor.assessments.score', [$period, $teacher]) }}">
                                            @if($status === 'draft')
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Lanjutkan
                                            @else
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Mulai Nilai
                                            @endif
                                        </x-ui.button>
                                    @else
                                        <span class="text-sm text-base-content/50">Periode ditutup</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            @php
                $totalTeachers = $teachers->count();
                $pending = $teachers->filter(fn($t) => !isset($existingAssessments[$t->id]) || $existingAssessments[$t->id]->status === 'pending')->count();
                $draft = $teachers->filter(fn($t) => isset($existingAssessments[$t->id]) && $existingAssessments[$t->id]->status === 'draft')->count();
                $completed = $teachers->filter(fn($t) => isset($existingAssessments[$t->id]) && in_array($existingAssessments[$t->id]->status, ['submitted', 'finalized']))->count();
            @endphp
            <div class="stat bg-base-100 rounded-box shadow">
                <div class="stat-title">Total Guru</div>
                <div class="stat-value text-primary">{{ $totalTeachers }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow">
                <div class="stat-title">Belum Dinilai</div>
                <div class="stat-value text-base-content/50">{{ $pending }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow">
                <div class="stat-title">Draft</div>
                <div class="stat-value text-warning">{{ $draft }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow">
                <div class="stat-title">Selesai</div>
                <div class="stat-value text-success">{{ $completed }}</div>
            </div>
        </div>
    @endif

</x-layouts.assessor>
