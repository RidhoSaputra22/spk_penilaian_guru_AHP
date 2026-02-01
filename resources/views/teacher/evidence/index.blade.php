<x-layouts.teacher>
    <x-slot:title>Upload Bukti</x-slot:title>

    <x-slot:breadcrumbs>
        <li>Upload Bukti</li>
    </x-slot:breadcrumbs>

    <x-slot:header>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Upload Bukti</h1>
                <p class="text-base-content/70 mt-1">Upload bukti/dokumen pendukung untuk indikator penilaian</p>
            </div>
        </div>
    </x-slot:header>

    @if($assessments->isEmpty())
        <x-ui.card>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <h3 class="text-lg font-semibold mb-2">Tidak Ada Indikator yang Membutuhkan Bukti</h3>
                <p class="text-base-content/60">Saat ini tidak ada penilaian aktif yang memerlukan upload bukti.</p>
            </div>
        </x-ui.card>
    @else
        @foreach($assessments as $assessment)
            @php
                $formVersion = $assessment->assignment->formVersion;
                $itemsRequiringEvidence = $formVersion->sections->flatMap->items->filter(fn($item) => $item->requires_evidence);
            @endphp

            @if($itemsRequiringEvidence->isNotEmpty())
                <x-ui.card class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="card-title">{{ $assessment->period->name ?? '-' }}</h2>
                            <p class="text-sm text-base-content/60">
                                {{ $formVersion->template->name ?? '-' }}
                            </p>
                        </div>
                        @if($assessment->period->status === 'active')
                            <x-ui.badge type="success" size="sm">Periode Aktif</x-ui.badge>
                        @else
                            <x-ui.badge type="ghost" size="sm">Periode Ditutup</x-ui.badge>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @foreach($formVersion->sections as $section)
                            @php
                                $sectionItems = $section->items->filter(fn($item) => $item->requires_evidence);
                            @endphp

                            @if($sectionItems->isNotEmpty())
                                <div class="collapse collapse-arrow bg-base-200">
                                    <input type="checkbox" checked />
                                    <div class="collapse-title font-medium">
                                        {{ $section->title }}
                                        <span class="text-sm text-base-content/60 ml-2">
                                            ({{ $sectionItems->count() }} indikator)
                                        </span>
                                    </div>
                                    <div class="collapse-content">
                                        <div class="space-y-4 pt-2">
                                            @foreach($sectionItems as $item)
                                                @php
                                                    $evidenceKey = $assessment->id . '-' . $item->id;
                                                    $existingEvidence = $evidenceUploads[$evidenceKey] ?? null;
                                                @endphp
                                                <div class="p-4 bg-base-100 rounded-lg border border-base-300">
                                                    <div class="flex items-start justify-between gap-4 mb-3">
                                                        <div>
                                                            <h4 class="font-medium">{{ $item->label }}</h4>
                                                            @if($item->description)
                                                                <p class="text-sm text-base-content/60 mt-1">{{ $item->description }}</p>
                                                            @endif
                                                        </div>
                                                        @if($existingEvidence)
                                                            <x-ui.badge type="success" size="sm">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                                Sudah Upload
                                                            </x-ui.badge>
                                                        @else
                                                            <x-ui.badge type="warning" size="sm">Belum Upload</x-ui.badge>
                                                        @endif
                                                    </div>

                                                    @if($existingEvidence)
                                                        <!-- Show existing evidence -->
                                                        <div class="flex items-center gap-4 p-3 bg-base-200 rounded-lg mb-3">
                                                            @if($existingEvidence->file_path)
                                                                <div class="flex items-center gap-2 flex-1">
                                                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                    <div>
                                                                        <p class="font-medium text-sm">{{ $existingEvidence->file_name }}</p>
                                                                        <p class="text-xs text-base-content/60">
                                                                            {{ number_format($existingEvidence->file_size / 1024, 2) }} KB
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <x-ui.button type="ghost" size="sm" href="{{ route('teacher.evidence.download', $existingEvidence) }}">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                    </svg>
                                                                </x-ui.button>
                                                            @endif
                                                            @if($existingEvidence->link)
                                                                <a href="{{ $existingEvidence->link }}" target="_blank" class="link link-primary text-sm">
                                                                    {{ Str::limit($existingEvidence->link, 50) }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                        @if($existingEvidence->description)
                                                            <p class="text-sm text-base-content/60 mb-3">
                                                                <strong>Keterangan:</strong> {{ $existingEvidence->description }}
                                                            </p>
                                                        @endif
                                                    @endif

                                                    @if($assessment->period->status === 'active')
                                                        <!-- Upload form -->
                                                        <form action="{{ route('teacher.evidence.upload', [$assessment, $item]) }}"
                                                            method="POST"
                                                            enctype="multipart/form-data"
                                                            class="space-y-3">
                                                            @csrf
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                                <x-ui.file
                                                                    name="file"
                                                                    label="File Bukti"
                                                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                                    helpText="Format: PDF, DOC, DOCX, JPG, PNG. Maks: 10MB"
                                                                />
                                                                <x-ui.input
                                                                    name="link"
                                                                    label="Atau Link URL"
                                                                    type="url"
                                                                    placeholder="https://..."
                                                                    helpText="Alternatif: masukkan link Google Drive, dll"
                                                                />
                                                            </div>
                                                            <x-ui.textarea
                                                                name="description"
                                                                label="Keterangan (Opsional)"
                                                                rows="2"
                                                                placeholder="Tambahkan keterangan untuk bukti ini..."
                                                            />
                                                            <div class="flex gap-2">
                                                                <x-ui.button type="primary" size="sm">
                                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                                    </svg>
                                                                    {{ $existingEvidence ? 'Ganti Bukti' : 'Upload Bukti' }}
                                                                </x-ui.button>
                                                                @if($existingEvidence)
                                                                    <form action="{{ route('teacher.evidence.destroy', $existingEvidence) }}" method="POST" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <x-ui.button type="error" size="sm" onclick="return confirm('Hapus bukti ini?')">
                                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                            </svg>
                                                                            Hapus
                                                                        </x-ui.button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </form>
                                                    @else
                                                        <x-ui.alert type="warning">
                                                            Periode penilaian sudah ditutup. Upload bukti tidak dapat dilakukan.
                                                        </x-ui.alert>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        @endforeach
    @endif

</x-layouts.teacher>
