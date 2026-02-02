<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\EvidenceUpload;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;
    protected Assessment $assessment;
    protected KpiFormItem $formItem;
    protected AssessmentItemValue $assessmentItemValue;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $this->teacher = User::factory()->create();
        $this->teacher->roles()->attach($role);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacher->id]);

        // Create form structure
        $template = KpiFormTemplate::factory()->create();
        $version = KpiFormVersion::factory()->create(['template_id' => $template->id]);
        $section = KpiFormSection::factory()->create(['form_version_id' => $version->id]);
        $this->formItem = KpiFormItem::factory()->create(['section_id' => $section->id]);

        // Create assessment
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        $this->assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'status' => 'draft',
        ]);

        // Create assessment item value for evidence uploads
        $this->assessmentItemValue = AssessmentItemValue::factory()->create([
            'assessment_id' => $this->assessment->id,
            'form_item_id' => $this->formItem->id,
        ]);
    }

    /** @test */
    public function teacher_can_view_evidence_page(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.evidence.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.evidence.index');
    }

    /** @test */
    public function teacher_can_upload_document_evidence(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'type' => 'document',
                'file' => $file,
                'description' => 'Test document evidence',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'assessment_item_value_id' => $this->assessmentItemValue->id,
            'mime_type' => 'application/pdf',
        ]);
    }

    /** @test */
    public function teacher_can_upload_photo_evidence(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'assessment_item_value_id' => $this->assessmentItemValue->id,
                'type' => 'photo',
                'file' => $file,
                'description' => 'Test photo evidence',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'assessment_item_value_id' => $this->assessmentItemValue->id,
        ]);
    }

    /** @test */
    public function teacher_can_add_link_evidence(): void
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'assessment_item_value_id' => $this->assessmentItemValue->id,
                'type' => 'link',
                'url' => 'https://example.com/evidence',
                'description' => 'Test link evidence',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evidence_uploads', [
            'assessment_item_value_id' => $this->assessmentItemValue->id,
            'url' => 'https://example.com/evidence',
        ]);
    }

    /** @test */
    public function teacher_can_delete_own_evidence(): void
    {
        $evidence = EvidenceUpload::factory()->create([
            'assessment_item_value_id' => $this->assessmentItemValue->id,
            'uploaded_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->delete(route('teacher.evidence.destroy', $evidence));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('evidence_uploads', ['id' => $evidence->id]);
    }

    /** @test */
    public function teacher_cannot_delete_others_evidence(): void
    {
        $otherUser = User::factory()->create();
        $otherItemValue = AssessmentItemValue::factory()->create();
        $evidence = EvidenceUpload::factory()->create([
            'assessment_item_value_id' => $otherItemValue->id,
            'uploaded_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->delete(route('teacher.evidence.destroy', $evidence));

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_download_evidence(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $path = $file->store('evidence');

        $evidence = EvidenceUpload::factory()->create([
            'assessment_item_value_id' => $this->assessmentItemValue->id,
            'uploaded_by' => $this->teacher->id,
            'path' => $path,
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.evidence.download', $evidence));

        $response->assertStatus(200);
    }

    /** @test */
    public function evidence_upload_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'assessment_item_value_id' => $this->assessmentItemValue->id,
                'type' => 'document',
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    /** @test */
    public function evidence_upload_validates_file_size(): void
    {
        $file = UploadedFile::fake()->create('large.pdf', 51200, 'application/pdf'); // 50MB

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'assessment_item_value_id' => $this->assessmentItemValue->id,
                'type' => 'document',
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    /** @test */
    public function teacher_cannot_upload_to_submitted_assessment(): void
    {
        $this->assessment->update(['status' => 'finalized']);

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [
                'assessment' => $this->assessment,
                'item' => $this->formItem
            ]), [
                'assessment_item_value_id' => $this->assessmentItemValue->id,
                'type' => 'document',
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }
}
