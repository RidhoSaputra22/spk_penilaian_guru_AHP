<x-layouts.admin>
    <div x-data="{
        {{-- State --}}
        step: 1,
        searchTerm: '',
        selectedTeachers: [],
        filterSubject: '',
        selectedGroupIds: [],

        {{-- Existing assignments data from server --}}
        existingAssignments: @js($existingAssignments),

        {{-- All teachers data --}}
        allTeachers: @js($teachers),

        {{-- Teacher groups --}}
        teacherGroups: @js($teacherGroups->map(fn($g) => [
            'id' => $g->id,
            'name' => $g->name,
            'teacher_ids' => $g->teachers->pluck('id')->toArray(),
        ])),

        {{-- Selections --}}
        periodId: '{{ old('assessment_period_id', '') }}',
        formVersionId: '{{ old('form_version_id', '') }}',
        assessorId: '{{ old('assessor_profile_id', '') }}',

        {{-- Computed: unique subjects --}}
        get subjects() {
            const subs = [...new Set(this.allTeachers.map(t => t.subject))].filter(Boolean).sort();
            return subs;
        },

        {{-- Computed: filtered teachers by search + subject --}}
        get filteredTeachers() {
            return this.allTeachers.filter(t => {
                const matchSearch = !this.searchTerm ||
                    t.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    t.employee_no.toLowerCase().includes(this.searchTerm.toLowerCase());
                const matchSubject = !this.filterSubject || t.subject === this.filterSubject;
                return matchSearch && matchSubject;
            });
        },

        {{-- Check if teacher already assigned for selected period+assessor --}}
        isAlreadyAssigned(teacherId) {
            if (!this.periodId || !this.assessorId) return false;
            const periodData = this.existingAssignments[this.periodId];
            if (!periodData) return false;
            const assessorData = periodData[this.assessorId];
            if (!assessorData) return false;
            return assessorData.includes(teacherId);
        },

        {{-- Toggle teacher selection --}}
        toggleTeacher(teacherId) {
            if (this.isAlreadyAssigned(teacherId)) return;
            const idx = this.selectedTeachers.indexOf(teacherId);
            if (idx > -1) {
                this.selectedTeachers.splice(idx, 1);
            } else {
                this.selectedTeachers.push(teacherId);
            }
        },

        {{-- Check if teacher is selected --}}
        isSelected(teacherId) {
            return this.selectedTeachers.includes(teacherId);
        },

        {{-- Select all visible (non-assigned) teachers --}}
        selectAll() {
            this.filteredTeachers.forEach(t => {
                if (!this.isAlreadyAssigned(t.id) && !this.selectedTeachers.includes(t.id)) {
                    this.selectedTeachers.push(t.id);
                }
            });
        },

        {{-- Deselect all visible teachers --}}
        deselectAll() {
            const visibleIds = this.filteredTeachers.map(t => t.id);
            this.selectedTeachers = this.selectedTeachers.filter(id => !visibleIds.includes(id));
            this.selectedGroupIds = [];
        },

        {{-- Select by group (toggle) --}}
        selectGroup(groupId) {
            const group = this.teacherGroups.find(g => g.id === groupId);
            if (!group) return;

            // Toggle group in selected groups array
            const idx = this.selectedGroupIds.indexOf(groupId);
            if (idx > -1) {
                // Deselect: remove group and unselect its teachers
                this.selectedGroupIds.splice(idx, 1);
                group.teacher_ids.forEach(tid => {
                    const teacherIdx = this.selectedTeachers.indexOf(tid);
                    if (teacherIdx > -1) {
                        this.selectedTeachers.splice(teacherIdx, 1);
                    }
                });
            } else {
                // Select: add group and select its teachers
                this.selectedGroupIds.push(groupId);
                group.teacher_ids.forEach(tid => {
                    if (!this.isAlreadyAssigned(tid) && !this.selectedTeachers.includes(tid)) {
                        this.selectedTeachers.push(tid);
                    }
                });
            }
        },

        {{-- Get teacher name by id --}}
        getTeacherName(id) {
            const t = this.allTeachers.find(t => t.id === id);
            return t ? t.name : id;
        },

        {{-- Count available (non-assigned) filtered teachers --}}
        get availableCount() {
            return this.filteredTeachers.filter(t => !this.isAlreadyAssigned(t.id)).length;
        },

        {{-- Check if all available visible teachers are selected --}}
        get allVisibleSelected() {
            const available = this.filteredTeachers.filter(t => !this.isAlreadyAssigned(t.id));
            if (available.length === 0) return false;
            return available.every(t => this.selectedTeachers.includes(t.id));
        },

        {{-- Validate step 1 --}}
        get step1Valid() {
            return this.periodId && this.formVersionId && this.assessorId;
        },

        {{-- Go to step 2 --}}
        goToStep2() {
            if (this.step1Valid) {
                this.step = 2;
            }
        },

        {{-- Go back to step 1, clear selection --}}
        goToStep1() {
            this.step = 1;
        },
    }" @select-change.window="
        if ($event.detail.name === 'assessment_period_id') periodId = $event.detail.value;
        if ($event.detail.name === 'form_version_id') formVersionId = $event.detail.value;
        if ($event.detail.name === 'assessor_profile_id') assessorId = $event.detail.value;
    " class="space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold">Penugasan Massal</h1>
                <p class="text-base-content/70 mt-1">Tugaskan formulir KPI ke banyak guru sekaligus</p>
            </div>
            <a href="{{ route('admin.kpi-assignments.index') }}" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-end gap-2">
            <div class="flex  items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                    :class="step >= 1 ? 'bg-primary text-primary-content' : 'bg-base-300 text-base-content/50'">
                    1
                </div>
                <span class="text-sm font-medium hidden sm:inline"
                    :class="step >= 1 ? 'text-primary' : 'text-base-content/50'">Konfigurasi</span>
            </div>
            <div class="w-12 h-0.5 bg-base-300">
                <div class="h-full bg-primary transition-all" :style="step >= 2 ? 'width: 100%' : 'width: 0%'"></div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                    :class="step >= 2 ? 'bg-primary text-primary-content' : 'bg-base-300 text-base-content/50'">
                    2
                </div>
                <span class="text-sm font-medium hidden sm:inline"
                    :class="step >= 2 ? 'text-primary' : 'text-base-content/50'">Pilih Guru</span>
            </div>
        </div>

        <!-- ========== STEP 1: Configuration ========== -->
        <div x-cloak x-show="step === 1" x-transition>
            <x-ui.card>
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Konfigurasi Penugasan
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Periode Penilaian -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Periode Penilaian Aktif <span
                                    class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="assessment_period_id" :options="$periods"
                            selected="{{ old('assessment_period_id') }}" placeholder="Pilih periode" required />
                        @error('assessment_period_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Formulir KPI -->
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Formulir KPI Aktif<span
                                    class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="form_version_id" :options="$formVersions"
                            selected="{{ old('form_version_id') }}" placeholder="Pilih formulir KPI" required />
                        @error('form_version_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Assessor -->
                    <div class="md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Penilai (Assessor) <span
                                    class="text-error">*</span></span>
                        </label>
                        <x-ui.select name="assessor_profile_id" :options="$assessors"
                            selected="{{ old('assessor_profile_id') }}" placeholder="Pilih penilai" required />
                        @error('assessor_profile_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" class="btn btn-primary" @click="goToStep2()" :disabled="!step1Valid">
                        Lanjut Pilih Guru
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </x-ui.card>
        </div>

        <!-- ========== STEP 2: Select Teachers ========== -->
        <div x-cloak x-show="step === 2" x-transition>
            <form method="POST" action="{{ route('admin.kpi-assignments.bulk-store') }}">
                @csrf

                {{-- Hidden fields for step 1 selections --}}
                <input type="hidden" name="assessment_period_id" x-model="periodId">
                <input type="hidden" name="form_version_id" x-model="formVersionId">
                <input type="hidden" name="assessor_profile_id" x-model="assessorId">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Main: Teacher Selection -->
                    <div class="lg:col-span-2 space-y-4">
                        <x-ui.card>
                            <!-- Header with search & filter -->
                            <div
                                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                                <h2 class="text-lg font-bold flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Pilih Guru
                                    <span class="badge badge-primary badge-sm"
                                        x-text="selectedTeachers.length + ' dipilih'"></span>
                                </h2>
                                <button type="button" class="btn btn-ghost btn-sm" @click="goToStep1()">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Ubah Konfigurasi
                                </button>
                            </div>

                            <!-- Search & Filter Bar -->
                            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                                <div class="flex-1 relative">
                                    <input type="text" x-model="searchTerm" placeholder="Cari nama atau NIP guru..."
                                        class="input input-bordered w-full pl-10">
                                    <svg class="w-4 h-4 absolute left-3 top-3.5 text-base-content/40" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <select x-model="filterSubject" class="select select-bordered w-full sm:w-48">
                                    <option value="">Semua Mata Pelajaran</option>
                                    <template x-for="subject in subjects" :key="subject">
                                        <option :value="subject" x-text="subject"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="text-sm text-base-content/70">Aksi cepat:</span>
                                <button type="button" class="btn btn-outline btn-xs btn-primary" @click="selectAll()">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Pilih Semua
                                </button>
                                <button type="button" class="btn btn-outline btn-xs" @click="deselectAll()">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Batal Semua
                                </button>

                                {{-- Group buttons --}}
                                @if($teacherGroups->isNotEmpty())
                                <div class="divider divider-horizontal mx-0"></div>
                                <span class="text-sm text-base-content/70">Grup:</span>
                                <template x-for="group in teacherGroups" :key="group.id">
                                    <button type="button" class="btn btn-outline btn-xs"
                                        :class="selectedGroupIds.includes(group.id) ? 'btn-primary' : 'btn-secondary'"
                                        @click="selectGroup(group.id)">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span x-text="group.name"></span>
                                    </button>
                                </template>
                                @endif
                            </div>

                            <!-- Select All Checkbox Header -->
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg mb-2">
                                <input type="checkbox" class="checkbox checkbox-primary checkbox-sm"
                                    :checked="allVisibleSelected && availableCount > 0"
                                    @click="allVisibleSelected ? deselectAll() : selectAll()">
                                <span class="text-sm font-semibold">
                                    Pilih semua guru yang terlihat
                                    (<span x-text="availableCount"></span> tersedia)
                                </span>
                            </div>

                            <!-- Teacher List -->
                            <div class="divide-y divide-base-200 max-h-[500px] overflow-y-auto">
                                <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                    <div class="flex items-center gap-3 p-3 rounded-lg transition-colors cursor-pointer hover:bg-base-200/50"
                                        :class="{
                                            'bg-primary/5 border-l-4 border-primary': isSelected(teacher.id),
                                            'opacity-50 cursor-not-allowed': isAlreadyAssigned(teacher.id),
                                        }" @click="toggleTeacher(teacher.id)">

                                        {{-- Checkbox --}}
                                        <input type="checkbox" class="checkbox checkbox-primary checkbox-sm"
                                            :checked="isSelected(teacher.id)" :disabled="isAlreadyAssigned(teacher.id)"
                                            @click.stop="toggleTeacher(teacher.id)">

                                        {{-- Hidden form field --}}
                                        <template x-if="isSelected(teacher.id)">
                                            <input type="hidden" name="teacher_ids[]" :value="teacher.id">
                                        </template>

                                        {{-- Avatar --}}
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-primary text-primary-content w-10 h-10 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-bold"
                                                    x-text="teacher.name.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                        </div>

                                        {{-- Teacher Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-sm" x-text="teacher.name"></div>
                                            <div class="flex flex-wrap gap-2 mt-0.5">
                                                <span class="text-xs text-base-content/60"
                                                    x-text="teacher.employee_no"></span>
                                                <span class="badge badge-ghost badge-xs"
                                                    x-text="teacher.subject"></span>
                                            </div>
                                        </div>

                                        {{-- Status Badge --}}
                                        <div x-show="isAlreadyAssigned(teacher.id)">
                                            <span class="badge badge-warning badge-sm gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Sudah ditugaskan
                                            </span>
                                        </div>

                                        <div x-show="isSelected(teacher.id) && !isAlreadyAssigned(teacher.id)">
                                            <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                                            </svg>
                                        </div>
                                    </div>
                                </template>

                                {{-- Empty State --}}
                                <div x-show="filteredTeachers.length === 0" class="py-8 text-center">
                                    <svg class="w-12 h-12 mx-auto text-base-content/30" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <p class="mt-2 text-base-content/60">Tidak ada guru yang cocok dengan pencarian</p>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>

                    <!-- Sidebar: Summary & Submit -->
                    <div class="space-y-4">
                        {{-- Summary Card --}}
                        <x-ui.card class="sticky top-4">
                            <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Ringkasan
                            </h3>

                            {{-- Stats --}}
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Guru dipilih</span>
                                    <span class="font-bold text-lg text-primary"
                                        x-text="selectedTeachers.length"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Total guru</span>
                                    <span class="text-sm" x-text="allTeachers.length"></span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-base-200">
                                    <span class="text-sm text-base-content/70">Sudah ditugaskan</span>
                                    <span class="text-sm text-warning"
                                        x-text="allTeachers.filter(t => isAlreadyAssigned(t.id)).length"></span>
                                </div>
                            </div>

                            {{-- Selected Teachers Preview --}}
                            <div class="mt-4" x-show="selectedTeachers.length > 0">
                                <h4 class="text-sm font-semibold mb-2 text-base-content/70">Guru yang dipilih:</h4>
                                <div class="max-h-48 overflow-y-auto space-y-1">
                                    <template x-for="tid in selectedTeachers" :key="tid">
                                        <div
                                            class="flex items-center justify-between bg-base-200 rounded-lg px-3 py-1.5">
                                            <span class="text-xs font-medium truncate"
                                                x-text="getTeacherName(tid)"></span>
                                            <button type="button" class="btn btn-ghost btn-xs"
                                                @click="toggleTeacher(tid)" title="Hapus">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Empty state --}}
                            <div x-show="selectedTeachers.length === 0" class="mt-4 text-center py-4">
                                <svg class="w-10 h-10 mx-auto text-base-content/20" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-xs text-base-content/50 mt-2">Belum ada guru dipilih</p>
                            </div>

                            {{-- Submit Button --}}
                            <div class="mt-6 space-y-2">
                                <button type="submit" class="btn btn-primary w-full"
                                    :disabled="selectedTeachers.length === 0">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Tugaskan <span x-text="selectedTeachers.length"></span> Guru
                                </button>
                                <a href="{{ route('admin.kpi-assignments.index') }}"
                                    class="btn btn-ghost w-full btn-sm">
                                    Batal
                                </a>
                            </div>
                        </x-ui.card>

                        {{-- Info Card --}}
                        <x-ui.card class="bg-info/5">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-info shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-base-content/70 space-y-1">
                                    <p class="font-semibold text-info">Tips Penugasan Massal</p>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        <li>Gunakan pencarian untuk menemukan guru tertentu</li>
                                        <li>Filter berdasarkan mata pelajaran untuk cepat memilih</li>
                                        <li>Guru yang sudah ditugaskan ditandai kuning dan tidak bisa dipilih ulang</li>
                                        <li>Klik baris guru atau checkbox untuk memilih/batalkan</li>
                                    </ul>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            </form>
        </div>

        {{-- Validation error for teacher_ids --}}
        @error('teacher_ids')
        <x-ui.alert type="error">{{ $message }}</x-ui.alert>
        @enderror
    </div>
</x-layouts.admin>