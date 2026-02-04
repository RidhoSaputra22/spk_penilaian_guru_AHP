<x-ui.card title="Pengaturan Notifikasi">
    <p class="text-sm text-base-content/70 mb-4">Kelola notifikasi sistem dan reminder</p>

    <form action="{{ route('admin.settings.update-notifications') }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        @php
            $notifSettings = $institution->meta['notification_settings'] ?? [];
        @endphp

        <div class="space-y-4">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="enable_email_notifications" value="1"
                    {{ ($notifSettings['enable_email_notifications'] ?? true) ? 'checked' : '' }}
                    class="checkbox checkbox-primary" />
                <div>
                    <div class="font-semibold">Aktifkan Notifikasi Email</div>
                    <div class="text-sm text-base-content/70">Kirim notifikasi melalui email</div>
                </div>
            </label>

            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="enable_assessment_reminders" value="1"
                    {{ ($notifSettings['enable_assessment_reminders'] ?? true) ? 'checked' : '' }}
                    class="checkbox checkbox-primary" />
                <div>
                    <div class="font-semibold">Reminder Penilaian</div>
                    <div class="text-sm text-base-content/70">Ingatkan penilai yang belum menyelesaikan penilaian</div>
                </div>
            </label>

            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="enable_result_notifications" value="1"
                    {{ ($notifSettings['enable_result_notifications'] ?? true) ? 'checked' : '' }}
                    class="checkbox checkbox-primary" />
                <div>
                    <div class="font-semibold">Notifikasi Hasil</div>
                    <div class="text-sm text-base-content/70">Beri tahu guru ketika hasil penilaian tersedia</div>
                </div>
            </label>

            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="enable_deadline_alerts" value="1"
                    {{ ($notifSettings['enable_deadline_alerts'] ?? true) ? 'checked' : '' }}
                    class="checkbox checkbox-primary" />
                <div>
                    <div class="font-semibold">Alert Deadline</div>
                    <div class="text-sm text-base-content/70">Peringatan menjelang deadline periode penilaian</div>
                </div>
            </label>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <x-ui.button type="primary" :isSubmit="true">Simpan Perubahan</x-ui.button>
        </div>
    </form>
</x-ui.card>
