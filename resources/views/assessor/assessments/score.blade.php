<x-layouts.assessor>
    <x-slot:title>Penilaian {{ $teacher->user->name ?? 'Guru' }}</x-slot:title>

    <x-slot:breadcrumbs>
        <li><a href="{{ route('assessor.assessments.index') }}">Penilaian</a></li>
        <li><a href="{{ route('assessor.assessments.period', $period) }}">{{ $period->name }}</a></li>
        <li>{{ $teacher->user->name ?? 'Guru' }}</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Form Penilaian KPI</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $formVersion->template->name ?? 'KPI Form' }} - Versi {{ $formVersion->version ?? '1' }}
                </p>
            </div>
            <x-ui.button type="ghost" href="{{ route('assessor.assessments.period', $period) }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </x-ui.button>
        </div>
    </x-slot:header>

    <!-- Teacher Info Card -->
    <x-ui.card class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-16 flex items-center justify-center">
                    <span class="text-2xl">{{ substr($teacher->user->name ?? '?', 0, 1) }}</span>
                </div>
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold">{{ $teacher->user->name ?? '-' }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2 text-sm">
                    <div>
                        <span class="text-base-content/60">NIP/NIK:</span>
                        <span class="font-medium ml-1">{{ $teacher->employee_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Mata Pelajaran:</span>
                        <span class="font-medium ml-1">{{ $teacher->subject ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Status:</span>
                        <x-ui.badge type="{{ $assessment->status === 'draft' ? 'warning' : 'ghost' }}" size="sm"
                            class="ml-1">
                            {{ ucfirst($assessment->status) }}
                        </x-ui.badge>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <!-- Assessment Form -->
    <form id="assessmentForm" action="{{ route('assessor.assessments.submit', $assessment) }}" method="POST"
        x-data="assessmentForm()" @submit.prevent="submitForm">
        @csrf

        @if($formVersion->sections->isEmpty())
        <x-ui.alert type="warning">
            Form KPI belum memiliki section/item. Hubungi administrator.
        </x-ui.alert>
        @else
        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="flex justify-between text-sm mb-2">
                <span>Progress Pengisian</span>
                <span x-text="filledCount + '/' + totalItems + ' indikator'"></span>
            </div>
            <progress class="progress progress-primary w-full" :value="filledCount" :max="totalItems"></progress>
        </div>

        <!-- Sections -->
        <div class="space-y-6">
            @foreach($formVersion->sections as $sectionIndex => $section)
            <x-ui.card>
                <div class="flex items-start gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold">
                        {{ $sectionIndex + 1 }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">{{ $section->title }}</h3>
                        @if($section->description)
                        <p class="text-sm text-base-content/60">{{ $section->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="divider my-2"></div>

                @if($section->items->isEmpty())
                <p class="text-base-content/50 text-center py-4">Tidak ada indikator di section ini</p>
                @else
                <div class="space-y-6">
                    @foreach($section->items as $itemIndex => $item)
                    @php
                    $existingValue = $existingValues[$item->id] ?? null;
                    $fieldId = "item_{$item->id}";
                    @endphp
                    <div class="p-4 rounded-lg bg-base-200/50 border border-base-200"
                        x-data="{ showNotes: {{ $existingValue?->notes ? 'true' : 'false' }} }">
                        <!-- Item Label -->
                        <div class="flex justify-between items-start mb-3">
                            <label class="font-medium" for="{{ $fieldId }}">
                                {{ $sectionIndex + 1 }}.{{ $itemIndex + 1 }}. {{ $item->label }}
                                @if($item->is_required)
                                <span class="text-error">*</span>
                                @endif
                            </label>
                            <button type="button" @click="showNotes = !showNotes" class="btn btn-ghost btn-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                Catatan
                            </button>
                        </div>

                        @if($item->help_text)
                        <p class="text-sm text-base-content/60 mb-3">{{ $item->help_text }}</p>
                        @endif

                        <!-- Field Input Based on Type -->
                        @switch($item->field_type)
                        @case('number')
                        @case('range')
                        <div class="flex items-center gap-4">
                            <input type="range" name="values[{{ $item->id }}]" id="{{ $fieldId }}"
                                min="{{ $item->min_value ?? 0 }}" max="{{ $item->max_value ?? 100 }}"
                                value="{{ $existingValue?->value_number ?? $item->default_value ?? ($item->min_value ?? 0) }}"
                                class="range range-primary flex-1" @input="updateProgress()"
                                x-ref="range_{{ $item->id }}"
                                oninput="document.getElementById('value_display_{{ $item->id }}').innerText = this.value" />
                            <div class="badge badge-lg badge-primary" id="value_display_{{ $item->id }}">
                                {{ $existingValue?->value_number ?? $item->default_value ?? ($item->min_value ?? 0) }}
                            </div>
                        </div>
                        <div class="flex justify-between text-xs text-base-content/50 mt-1">
                            <span>{{ $item->min_value ?? 0 }}</span>
                            <span>{{ $item->max_value ?? 100 }}</span>
                        </div>
                        @break

                        @case('select')
                        @case('radio')
                        @if($item->scale && $item->scale->options->isNotEmpty())
                        <!-- Using Scoring Scale -->
                        <div class="grid grid-cols-2 md:grid-cols-{{ min($item->scale->options->count(), 5) }} gap-2">
                            @foreach($item->scale->options as $option)
                            <label class="cursor-pointer">
                                <input type="radio" name="values[{{ $item->id }}]" value="{{ $option->numeric_value }}"
                                    class="peer hidden" @change="updateProgress()"
                                    {{ ($existingValue?->value_number == $option->numeric_value) ? 'checked' : '' }}
                                    {{ $item->is_required ? 'required' : '' }} />
                                <div
                                    class="p-3 rounded-lg border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary/10 text-center transition-all hover:border-primary/50">
                                    <div class="font-bold text-lg">{{ $option->numeric_value }}</div>
                                    <div class="text-xs text-base-content/60">{{ $option->label }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @elseif($item->options->isNotEmpty())
                        <!-- Using Item Options -->
                        <div class="space-y-2">
                            @foreach($item->options as $option)
                            <label
                                class="flex items-center gap-3 p-3 rounded-lg border border-base-300 cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="values[{{ $item->id }}]" value="{{ $option->value }}"
                                    class="radio radio-primary" @change="updateProgress()"
                                    {{ ($existingValue?->value_string == $option->value || $existingValue?->value_number == $option->value) ? 'checked' : '' }}
                                    {{ $item->is_required ? 'required' : '' }} />
                                <span>{{ $option->label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <p class="text-warning text-sm">Opsi belum dikonfigurasi</p>
                        @endif
                        @break

                        @case('textarea')
                        <textarea name="values[{{ $item->id }}]" id="{{ $fieldId }}"
                            class="textarea textarea-bordered w-full" rows="3" @input="updateProgress()"
                            placeholder="Masukkan jawaban..."
                            {{ $item->is_required ? 'required' : '' }}>{{ $existingValue?->value_string ?? '' }}</textarea>
                        @break

                        @case('checkbox')
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="values[{{ $item->id }}]" value="1"
                                class="checkbox checkbox-primary" @change="updateProgress()"
                                {{ $existingValue?->value_bool ? 'checked' : '' }} />
                            <span>Ya / Memenuhi</span>
                        </label>
                        @break

                        @default
                        <!-- Default: Number Input -->
                        <input type="number" name="values[{{ $item->id }}]" id="{{ $fieldId }}"
                            min="{{ $item->min_value ?? 0 }}" max="{{ $item->max_value ?? 100 }}"
                            value="{{ $existingValue?->value_number ?? '' }}" class="input input-bordered w-full"
                            @input="updateProgress()"
                            placeholder="Masukkan nilai ({{ $item->min_value ?? 0 }} - {{ $item->max_value ?? 100 }})"
                            {{ $item->is_required ? 'required' : '' }} />
                        @endswitch

                        <!-- Notes Field -->
                        <div x-show="showNotes" x-collapse class="mt-3">
                            <textarea name="notes[{{ $item->id }}]"
                                class="textarea textarea-bordered w-full textarea-sm" rows="2"
                                placeholder="Catatan/komentar untuk indikator ini (opsional)">{{ $existingValue?->notes ?? '' }}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-ui.card>
            @endforeach
        </div>

        <!-- Action Buttons -->
        <div class="sticky bottom-4 mt-6">
            <x-ui.card class="!shadow-2xl border border-base-300">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-base-content/60">
                        <span x-show="!isSaving">Terakhir disimpan: <span x-text="lastSaved"></span></span>
                        <span x-show="isSaving" class="loading loading-spinner loading-sm"></span>
                        <span x-show="isSaving"> Menyimpan...</span>
                    </div>
                    <div class="flex gap-2">
                        <x-ui.button type="ghost" href="{{ route('assessor.assessments.period', $period) }}">
                            Batal
                        </x-ui.button>
                        <button type="button" class="btn btn-outline btn-primary" @click="saveDraft()"
                            :disabled="isSaving">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Simpan Draft
                        </button>
                        <button type="button" class="btn btn-primary" @click="confirmSubmit()" :disabled="isSaving">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Submit Penilaian
                        </button>
                    </div>
                </div>
            </x-ui.card>
        </div>
        @endif
    </form>

    <!-- Submit Confirmation Modal -->
    <x-ui.modal id="confirmSubmitModal" title="Konfirmasi Submit Penilaian">
        <div class="py-4">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-warning/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold">Yakin submit penilaian?</h4>
                    <p class="text-sm text-base-content/60">
                        Setelah disubmit, Anda tidak dapat mengubah nilai lagi.
                    </p>
                </div>
            </div>

            <div class="bg-base-200 rounded-lg p-4">
                <p class="text-sm"><strong>Guru:</strong> {{ $teacher->user->name ?? '-' }}</p>
                <p class="text-sm"><strong>Periode:</strong> {{ $period->name }}</p>
            </div>
        </div>

        <x-slot:actions>
            <form method="dialog">
                <x-ui.button type="ghost">Batal</x-ui.button>
            </form>
            <x-ui.button type="primary" onclick="document.getElementById('assessmentForm').submit()">
                Ya, Submit Sekarang
            </x-ui.button>
        </x-slot:actions>
    </x-ui.modal>

    @push('scripts')
    <script>
    function assessmentForm() {
        return {
            totalItems: {
                {
                    $formVersion - > sections - > flatMap - > items - > count()
                }
            },
            filledCount: 0,
            isSaving: false,
            lastSaved: 'Belum disimpan',

            init() {
                this.updateProgress();
                // Auto-save every 60 seconds
                setInterval(() => {
                    if (!this.isSaving) {
                        this.saveDraft(true);
                    }
                }, 60000);
            },

            updateProgress() {
                const form = document.getElementById('assessmentForm');
                const inputs = form.querySelectorAll(
                    'input[name^="values["], textarea[name^="values["], select[name^="values["]');
                let filled = 0;

                inputs.forEach(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        if (input.checked) filled++;
                    } else if (input.value && input.value.trim() !== '') {
                        filled++;
                    }
                });

                // Count unique filled items for radio buttons
                const radioGroups = {};
                form.querySelectorAll('input[type="radio"][name^="values["]:checked').forEach(radio => {
                    radioGroups[radio.name] = true;
                });
                filled = Object.keys(radioGroups).length;

                // Add other input types
                form.querySelectorAll(
                    'input[type="number"][name^="values["], input[type="range"][name^="values["], textarea[name^="values["]'
                    ).forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        filled++;
                    }
                });

                this.filledCount = filled;
            },

            async saveDraft(silent = false) {
                this.isSaving = true;
                const form = document.getElementById('assessmentForm');
                const formData = new FormData(form);

                try {
                    const response = await fetch('{{ route('
                        assessor.assessments.save - draft ', $assessment) }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        });

                    const data = await response.json();

                    if (data.success) {
                        this.lastSaved = new Date().toLocaleTimeString('id-ID');
                        if (!silent) {
                            // Show toast notification
                            this.showToast('Draft berhasil disimpan', 'success');
                        }
                    } else {
                        this.showToast(data.error || 'Gagal menyimpan', 'error');
                    }
                } catch (error) {
                    console.error('Save draft error:', error);
                    if (!silent) {
                        this.showToast('Terjadi kesalahan saat menyimpan', 'error');
                    }
                } finally {
                    this.isSaving = false;
                }
            },

            confirmSubmit() {
                document.getElementById('confirmSubmitModal').showModal();
            },

            submitForm() {
                document.getElementById('assessmentForm').submit();
            },

            showToast(message, type) {
                // Simple toast implementation
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} fixed bottom-20 right-4 w-auto max-w-sm z-50 shadow-lg`;
                toast.innerHTML = `<span>${message}</span>`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        }
    }
    </script>
    @endpush

</x-layouts.assessor>