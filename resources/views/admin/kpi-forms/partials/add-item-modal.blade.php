<!-- Add Item Modal -->
<x-ui.modal id="add-item-modal" title="Tambah Item Baru" size="lg">
    <form method="POST" action="{{ route('admin.kpi-forms.add-item', $version) }}" id="add-item-form" class="space-y-4">
        @csrf
        <input type="hidden" name="section_id" id="add-item-section-id">

        <!-- Item Label -->
        <x-ui.input name="label" label="Label Item" placeholder="Contoh: Kemampuan mengelola kelas dengan baik" required
            value="{{ old('label') }}" />

        <!-- Help Text -->
        <x-ui.textarea name="help_text" label="Teks Bantuan" rows="2" placeholder="Petunjuk pengisian untuk penilai..."
            value="{{ old('help_text') }}" />

        <!-- Field Type and Criteria -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.select name="field_type" label="Tipe Field" :options="$fieldTypes ?? []" required :searchable="false"
                selected="{{ old('field_type', 'numeric') }}" />

            @if(!empty($criteriaOptions))
            <x-ui.select name="criteria_node_id" label="Kriteria Terkait" :options="$criteriaOptions"
                selected="{{ old('criteria_node_id') }}" />
            @endif
        </div>

        <!-- Min/Max Values -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.input name="min_value" label="Nilai Minimum" type="number" placeholder="1" step="0.01"
                value="{{ old('min_value') }}" />
            <x-ui.input name="max_value" label="Nilai Maximum" type="number" placeholder="5" step="0.01"
                value="{{ old('max_value') }}" />
        </div>

        <!-- Sort Order -->
        <x-ui.input name="sort_order" label="Urutan dalam Seksi" type="number" placeholder="1"
            value="{{ old('sort_order', 1) }}" />

        <!-- Required Checkbox -->
        <x-ui.checkbox name="is_required" :options="[['value' => '1', 'label' => 'Wajib diisi oleh penilai']]"
            :checked="old('is_required') ? [old('is_required')] : ['1']" :single="true" />

        <!-- Help Text -->
        <x-ui.alert type="info">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Item adalah elemen penilaian individual yang akan diisi oleh penilai.</span>
        </x-ui.alert>

        <x-slot:actions>
            <button type="button" class="btn btn-ghost" onclick="closeAddItemModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="submitAddItemForm()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Simpan Item
            </button>
        </x-slot:actions>
    </form>
</x-ui.modal>

@if($errors->any() && old('section_id'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show modal if there are validation errors and section_id is set
    document.getElementById('add-item-section-id').value = `{{ old('section_id') }}`;
    document.getElementById('add-item-modal').showModal();
});
</script>
@endif

<script>
function closeAddItemModal() {
    document.getElementById('add-item-modal').close();
    // Reset form
    document.getElementById('add-item-form').reset();
    document.getElementById('add-item-section-id').value = '';
}

function submitAddItemForm() {
    const form = document.getElementById('add-item-form');
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;

    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Menyimpan...';

    // Validate required fields
    const labelField = form.querySelector('input[name="label"]');
    const fieldTypeField = form.querySelector('select[name="field_type"]');
    const sectionIdField = form.querySelector('input[name="section_id"]');

    if (!labelField.value.trim()) {
        alert('Label item harus diisi!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        labelField.focus();
        return;
    }

    if (!fieldTypeField.value) {
        alert('Tipe field harus dipilih!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        fieldTypeField.focus();
        return;
    }

    if (!sectionIdField.value) {
        alert('Section ID tidak ditemukan!');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        return;
    }

    // Submit form
    form.submit();
}
</script>
