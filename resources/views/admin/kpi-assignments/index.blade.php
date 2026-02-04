<x-layouts.admin>

    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Penugasan KPI</h1>
                <p class="text-base-content/70 mt-1">Kelola penugasan formulir KPI ke guru</p>
            </div>
            <x-ui.button variant="primary" size="sm" onclick="assignKpiModal.showModal()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tugaskan KPI
            </x-ui.button>
        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <x-ui.input type="search" placeholder="Cari guru atau formulir..." name="search"
                        value="{{ request('search') }}" />
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="period">
                        <option value="">Semua Periode</option>
                        <option value="period1" {{ request('period') == 'period1' ? 'selected' : '' }}>Periode 1 - 2024
                        </option>
                        <option value="period2" {{ request('period') == 'period2' ? 'selected' : '' }}>Periode 2 - 2024
                        </option>
                    </x-ui.select>
                </div>
                <div class="sm:w-48">
                    <x-ui.select name="status">
                        <option value="">Semua Status</option>
                        <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Ditugaskan
                        </option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                            Dikerjakan
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai
                        </option>
                    </x-ui.select>
                </div>
                <div class="flex gap-2">
                    <x-ui.button variant="outline" size="sm" type="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Cari
                    </x-ui.button>
                    <x-ui.button variant="ghost" size="sm"
                        onclick="window.location.href = '{{ route('admin.kpi-assignments.index') }}'">
                        Reset
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-ui.stat title="Total Penugasan" value="24" description="Formulir yang ditugaskan"
                icon="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            <x-ui.stat title="Ditugaskan" value="8" description="Menunggu dikerjakan"
                icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" class="text-warning" />
            <x-ui.stat title="Dikerjakan" value="12" description="Sedang dalam progress"
                icon="M13 10V3L4 14h7v7l9-11h-7z" class="text-info" />
            <x-ui.stat title="Selesai" value="4" description="Telah diselesaikan" icon="M5 13l4 4L19 7"
                class="text-success" />
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
                            <th>Deadline</th>
                            <th>Progress</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse([] as $key => $assignment)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content w-10 rounded-full">
                                            <span
                                                class="text-sm">{{ substr($assignment->teacher_name ?? 'JS', 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $assignment->teacher_name ?? 'John Smith' }}</div>
                                        <div class="text-sm text-base-content/70">
                                            {{ $assignment->employee_no ?? 'EMP001' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="font-semibold">
                                        {{ $assignment->form_name ?? 'Formulir Penilaian Kinerja' }}
                                    </div>
                                    <div class="text-sm text-base-content/70">v{{ $assignment->form_version ?? '1.0' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $assignment->period_name ?? 'Periode 1 - 2024' }}
                                    </div>
                                    <div class="text-base-content/70">
                                        {{ $assignment->period_range ?? 'Jan - Jun 2024' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                $status = $assignment->status ?? 'assigned';
                                $statusConfig = [
                                'assigned' => ['badge' => 'warning', 'text' => 'Ditugaskan'],
                                'in_progress' => ['badge' => 'info', 'text' => 'Dikerjakan'],
                                'completed' => ['badge' => 'success', 'text' => 'Selesai'],
                                'overdue' => ['badge' => 'error', 'text' => 'Terlambat'],
                                ];
                                @endphp
                                <x-ui.badge variant="{{ $statusConfig[$status]['badge'] }}">
                                    {{ $statusConfig[$status]['text'] }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="text-sm">
                                    {{ $assignment->deadline ?? now()->addDays(30)->format('d M Y') }}
                                </div>
                                @if($status === 'overdue')
                                <div class="text-xs text-error">Terlambat</div>
                                @endif
                            </td>
                            <td>
                                @php $progress = $assignment->progress ?? rand(0, 100); @endphp
                                <div class="flex items-center gap-2">
                                    <progress class="progress progress-primary w-16" value="{{ $progress }}"
                                        max="100"></progress>
                                    <span class="text-xs">{{ $progress }}%</span>
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
                                        <li><a href="#" onclick="viewDetailModal.showModal()">Lihat Detail</a></li>
                                        <li><a href="#">Edit Deadline</a></li>
                                        <li><a href="#">Kirim Reminder</a></li>
                                        <li><a class="text-error"
                                                onclick="removeAssignmentModal.showModal()">Batalkan</a>
                                        </li>
                                    </ul>
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
                                    <x-ui.button variant="primary" size="sm" onclick="assignKpiModal.showModal()">
                                        Tugaskan KPI
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>

    <!-- Assign KPI Modal -->
    <x-ui.modal id="assignKpiModal" title="Tugaskan Formulir KPI">
        <form method="POST" action="#" class="space-y-4">
            @csrf
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Formulir KPI <span class="text-error">*</span></span>
                </label>
                <x-ui.select name="kpi_form_id" required>
                    <option value="">Pilih formulir KPI</option>
                    <option value="form1">Formulir Penilaian Kinerja v1.0</option>
                    <option value="form2">Formulir Evaluasi Guru v2.1</option>
                    <option value="form3">Formulir Penilaian Tahunan v1.5</option>
                </x-ui.select>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Periode Penilaian <span class="text-error">*</span></span>
                </label>
                <x-ui.select name="period_id" required>
                    <option value="">Pilih periode</option>
                    <option value="period1">Periode 1 - 2024 (Jan - Jun)</option>
                    <option value="period2">Periode 2 - 2024 (Jul - Des)</option>
                </x-ui.select>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Guru <span class="text-error">*</span></span>
                </label>
                <x-ui.select name="teacher_id" required>
                    <option value="">Pilih guru</option>
                    <option value="teacher1">John Smith (EMP001)</option>
                    <option value="teacher2">Jane Doe (EMP002)</option>
                    <option value="teacher3">Bob Johnson (EMP003)</option>
                </x-ui.select>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Deadline <span class="text-error">*</span></span>
                </label>
                <x-ui.input type="date" name="deadline" value="{{ now()->addDays(30)->format('Y-m-d') }}" required />
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Catatan</span>
                </label>
                <x-ui.textarea name="notes" rows="3" placeholder="Catatan tambahan untuk penugasan ini..." />
            </div>

            <x-slot name="actions">
                <x-ui.button variant="outline" type="button" onclick="assignKpiModal.close()">
                    Batal
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    Tugaskan
                </x-ui.button>
            </x-slot>
        </form>
    </x-ui.modal>

    <!-- View Detail Modal -->
    <x-ui.modal id="viewDetailModal" title="Detail Penugasan KPI">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Guru</span>
                    </label>
                    <div class="text-sm">John Smith (EMP001)</div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Status</span>
                    </label>
                    <x-ui.badge variant="info">Dikerjakan</x-ui.badge>
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Formulir KPI</span>
                </label>
                <div class="text-sm">Formulir Penilaian Kinerja v1.0</div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Periode</span>
                    </label>
                    <div class="text-sm">Periode 1 - 2024</div>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Deadline</span>
                    </label>
                    <div class="text-sm">{{ now()->addDays(15)->format('d M Y') }}</div>
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Progress</span>
                </label>
                <div class="flex items-center gap-2">
                    <progress class="progress progress-primary flex-1" value="65" max="100"></progress>
                    <span class="text-sm font-semibold">65%</span>
                </div>
            </div>

            <div>
                <label class="label">
                    <span class="label-text font-semibold">Catatan</span>
                </label>
                <div class="text-sm bg-base-200 p-3 rounded-lg">
                    Penugasan ini untuk evaluasi kinerja semester pertama.
                </div>
            </div>
        </div>
        <x-slot name="actions">
            <x-ui.button variant="outline" type="button" onclick="viewDetailModal.close()">
                Tutup
            </x-ui.button>
        </x-slot>
    </x-ui.modal>

    <!-- Remove Assignment Modal -->
    <x-ui.modal id="removeAssignmentModal" title="Batalkan Penugasan">
        <form method="POST" action="#" class="space-y-4">
            @csrf
            @method('DELETE')
            <x-ui.alert type="warning">
                <strong>Peringatan!</strong> Tindakan ini akan membatalkan penugasan KPI dan menghapus semua progress
                yang
                sudah dibuat.
            </x-ui.alert>
            <p>Apakah Anda yakin ingin membatalkan penugasan ini?</p>

            <x-slot name="actions">
                <x-ui.button variant="outline" type="button" onclick="removeAssignmentModal.close()">
                    Batal
                </x-ui.button>
                <x-ui.button variant="error" type="submit">
                    Batalkan Penugasan
                </x-ui.button>
            </x-slot>
        </form>
    </x-ui.modal>
</x-layouts.admin>
