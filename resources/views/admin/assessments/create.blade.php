<x-layouts.admin>
    <x-slot:breadcrumbs>
        <li><a href="{{ route('admin.assessments.index') }}">Monitoring Penilaian</a></li>
        <li>Buat Penugasan</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <h1 class="text-2xl font-bold">Buat Penugasan Penilaian</h1>
        <p class="text-base-content/60">Tugaskan penilai untuk menilai guru</p>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.assessments.store') }}" method="POST">
                @csrf

                <x-ui.card title="Informasi Penugasan" class="mb-6">
                    <!-- Period Selection -->
                    <div class="mb-4">
                        <x-ui.select name="period_id" label="Periode Penilaian" :options="$periods"
                            selected="{{ old('period_id', $selectedPeriod?->id) }}" required />
                        @error('period_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Assignment Type -->
                    <div class="mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Tipe Penugasan</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="assignment_type" value="individual"
                                    class="radio radio-primary" checked onchange="toggleAssignmentType()" />
                                <span>Individual (Satu guru - Satu penilai)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="assignment_type" value="bulk" class="radio radio-primary"
                                    onchange="toggleAssignmentType()" />
                                <span>Massal (Beberapa guru - Beberapa penilai)</span>
                            </label>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Individual Assignment -->
                <div id="individual-assignment" class="space-y-6">
                    <x-ui.card title="Pilih Guru dan Penilai">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Teacher Selection -->
                            <div>
                                <x-ui.select name="teacher_id" label="Guru yang Dinilai" :options="$teachers"
                                    selected="{{ old('teacher_id') }}" />
                                @error('teacher_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Assessor Selection -->
                            <div>
                                <x-ui.select name="assessor_id" label="Penilai" :options="$assessors"
                                    selected="{{ old('assessor_id') }}" />
                                @error('assessor_id')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Bulk Assignment -->
                <div id="bulk-assignment" class="space-y-6 hidden">
                    <x-ui.card title="Pilih Guru (Multiple)">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Guru yang Dinilai</span>
                            </label>
                            <select name="teacher_ids[]" multiple size="8" class="select select-bordered w-full">
                                @foreach($teachers as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('teacher_ids', [])) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt">Tahan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih
                                    dari satu</span>
                            </label>
                            @error('teacher_ids')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-ui.card>

                    <x-ui.card title="Pilih Penilai (Multiple)">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Penilai</span>
                            </label>
                            <select name="assessor_ids[]" multiple size="8" class="select select-bordered w-full">
                                @foreach($assessors as $id => $name)
                                <option value="{{ $id }}"
                                    {{ in_array($id, old('assessor_ids', [])) ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt">Tahan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih
                                    dari satu</span>
                            </label>
                            @error('assessor_ids')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </x-ui.card>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 justify-end mt-6">
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-ghost">
                        Batal
                    </a>
                    <x-ui.button type="primary" :isSubmit="true">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Buat Penugasan
                    </x-ui.button>
                </div>
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="lg:col-span-1">
            <x-ui.card title="Informasi" compact>
                <div class="space-y-4 text-sm">
                    <div>
                        <h4 class="font-semibold mb-1">Penugasan Individual</h4>
                        <p class="text-base-content/70">Tugaskan satu penilai untuk menilai satu guru tertentu.</p>
                    </div>

                    <div class="divider my-2"></div>

                    <div>
                        <h4 class="font-semibold mb-1">Penugasan Massal</h4>
                        <p class="text-base-content/70">Tugaskan beberapa penilai untuk menilai beberapa guru sekaligus.
                            Setiap guru akan dinilai oleh semua penilai yang dipilih.</p>
                    </div>

                    <div class="divider my-2"></div>

                    <div>
                        <h4 class="font-semibold mb-1">Catatan</h4>
                        <ul class="list-disc list-inside text-base-content/70 space-y-1">
                            <li>Pastikan periode sudah dibuat</li>
                            <li>Pastikan form KPI sudah dipublish</li>
                            <li>Duplikasi penugasan akan diabaikan</li>
                        </ul>
                    </div>
                </div>
            </x-ui.card>

            @if($selectedPeriod)
            <x-ui.card title="Detail Periode" class="mt-4" compact>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Nama:</span>
                        <span class="font-medium">{{ $selectedPeriod->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Tahun:</span>
                        <span class="font-medium">{{ $selectedPeriod->academic_year }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Semester:</span>
                        <span class="font-medium">{{ $selectedPeriod->semester }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Status:</span>
                        <span>
                            @if($selectedPeriod->status === 'open')
                            <span class="badge badge-success badge-sm">Aktif</span>
                            @else
                            <span class="badge badge-ghost badge-sm">{{ ucfirst($selectedPeriod->status) }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </x-ui.card>
            @endif
        </div>
    </div>

    <script>
    function toggleAssignmentType() {
        const type = document.querySelector('input[name="assignment_type"]:checked').value;
        const individual = document.getElementById('individual-assignment');
        const bulk = document.getElementById('bulk-assignment');

        if (type === 'individual') {
            individual.classList.remove('hidden');
            bulk.classList.add('hidden');

            // Clear bulk selections
            document.querySelectorAll('#bulk-assignment select').forEach(select => {
                select.value = '';
            });
        } else {
            individual.classList.add('hidden');
            bulk.classList.remove('hidden');

            // Clear individual selections
            document.querySelectorAll('#individual-assignment select').forEach(select => {
                select.value = '';
            });
        }
    }
    </script>
</x-layouts.admin>
