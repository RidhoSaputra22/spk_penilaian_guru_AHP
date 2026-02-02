<!-- Add Section Modal -->
<x-ui.modal id="add-section-modal" title="Tambah Seksi Baru" size="xl">
    <form method="POST" action="{{ route('admin.kpi-forms.add-section', $version) }}" id="add-section-form"
        class="space-y-4 grid grid-cols-2 gap-5">
        @csrf

        <!-- Section Title -->
        <x-ui.input name="title" label="Judul Seksi" placeholder="Contoh: Kompetensi Pedagogik" required
            value="{{ old('title') }}" />

        <!-- Criteria Selection -->
        @if(!empty($criteriaOptions))
        <x-ui.select name="criteria_node_id" label="Kriteria (opsional)" :options="$criteriaOptions"
            selected="{{ old('criteria_node_id') }}" class="" />
        @endif

        <!-- Sort Order -->
        <x-ui.input name="sort_order" label="Urutan" type="number" placeholder="1"
            value="{{ old('sort_order', ($version->sections->count() ?? 0) + 1) }}" />

        <!-- Section Description -->
        <x-ui.textarea name="description" label="Deskripsi" rows="3" class="col-span-3"
            placeholder="Deskripsi singkat tentang seksi ini..." value="{{ old('description') }}" />





        <!-- Help Text -->
        <x-ui.alert type="info" class="col-span-3">

            <span>Seksi digunakan untuk mengelompokkan item penilaian berdasarkan kategori atau kriteria
                tertentu.</span>
        </x-ui.alert>

        <x-slot:actions>
            <button type="button" class="btn btn-ghost" onclick="closeAddSectionModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitAddSectionForm()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Simpan Seksi
            </button>
        </x-slot:actions>
    </form>
</x-ui.modal>

@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show modal if there are validation errors
    document.getElementById('add-section-modal').showModal();
});
</script>
@endif

<script>
function closeAddSectionModal() {
    document.getElementById('add-section-modal').close();
    // Reset form
    document.getElementById('add-section-form').reset();
}

function submitAddSectionForm() {
    const form = document.getElementById('add-section-form');
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;

    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Menyimpan...';

    // Validate required fields
    const titleField = form.querySelector('input[name="title"]');
    if (!titleField.value.trim()) {
        alert('Judul seksi harus diisi!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        titleField.focus();
        return;
    }

    // Submit form
    form.submit();
}
</script>
