<x-layouts.admin>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Tugaskan Formulir KPI</h1>
                <p class="text-base-content/70 mt-1">Buat penugasan formulir KPI baru untuk guru</p>
            </div>
            <a href="{{ route('admin.kpi-assignments.index') }}" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>

        <!-- Form -->
        <x-ui.card>
            <form method="POST" action="{{ route('admin.kpi-assignments.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Periode Penilaian -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Periode Penilaian <span
                                    class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="assessment_period_id" :options="$periods"
                            selected="{{ old('assessment_period_id') }}" placeholder="Pilih periode" required />
                        @error('assessment_period_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>

                    <!-- Formulir KPI -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Formulir KPI <span class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="form_version_id" :options="$formVersions"
                            selected="{{ old('form_version_id') }}" placeholder="Pilih formulir KPI" required />
                        @error('form_version_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>

                    <!-- Guru -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Guru <span class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="teacher_profile_id" :options="$teachers"
                            selected="{{ old('teacher_profile_id') }}" placeholder="Pilih guru" required />
                        @error('teacher_profile_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>

                    <!-- Assessor -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Penilai (Assessor) <span
                                    class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="assessor_profile_id" :options="$assessors"
                            selected="{{ old('assessor_profile_id') }}" placeholder="Pilih penilai" required />
                        @error('assessor_profile_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.kpi-assignments.index') }}" class="btn btn-ghost">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Tugaskan
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>
