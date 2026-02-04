<x-ui.card title="Informasi Institusi">
    <p class="text-sm text-base-content/70 mb-4">Pengaturan dasar institusi Anda</p>

    <form action="{{ route('admin.settings.update-institution') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-ui.input type="text" name="name" label="Nama Institusi"
                    value="{{ old('name', $institution->name ?? '') }}" required />
            </div>


            <div class="md:col-span-2">
                <x-ui.textarea name="address" label="Alamat" rows="2"
                    value="{{ old('address', $institution->address ?? '') }}" />
            </div>


        </div>

        <div class="flex justify-end">
            <x-ui.button type="primary" :isSubmit="true">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>