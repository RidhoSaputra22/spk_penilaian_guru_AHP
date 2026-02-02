<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\EvidenceUpload;
use App\Models\Institution;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\KpiFormVersion;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;
    protected Institution $institution;
    protected Role $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->institution = Institution::factory()->create();
        $this->teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $this->teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $this->teacher->roles()->attach($this->teacherRole);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacher->id]);
    }

    /** @test */
    public function teacher_can_access_evidence_index(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.evidence.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.evidence.index');
    }

    /** @test */
    public function guest_cannot_access_evidence(): void
    {
        $response = $this->get(route('teacher.evidence.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function evidence_index_displays_assessments(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.evidence.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assessments');
        $response->assertViewHas('evidenceUploads');
    }

    /** @test */
    public function teacher_can_upload_evidence_file(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $assignment = KpiFormAssignment::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $version = KpiFormVersion::factory()->create();
        $section = KpiFormSection::factory()->create(['form_version_id' => $version->id]);
        $item = KpiFormItem::factory()->create(['section_id' => $section->id]);

        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'assignment_id' => $assignment->id,
            'status' => 'draft',
        ]);

        $assessmentItemValue = AssessmentItemValue::factory()->create([
            'assessment_id' => $assessment->id,
            'form_item_id' => $item->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [$assessment, $item]), [
                'file' => $file,
                'description' => 'Test evidence description',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function teacher_can_upload_evidence_link(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $assignment = KpiFormAssignment::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $version = KpiFormVersion::factory()->create();
        $section = KpiFormSection::factory()->create(['form_version_id' => $version->id]);
        $item = KpiFormItem::factory()->create(['section_id' => $section->id]);

        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'assignment_id' => $assignment->id,
            'status' => 'draft',
        ]);

        $assessmentItemValue = AssessmentItemValue::factory()->create([
            'assessment_id' => $assessment->id,
            'form_item_id' => $item->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [$assessment, $item]), [
                'type' => 'link',
                'url' => 'https://example.com/evidence',
                'description' => 'Test evidence link',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function teacher_cannot_upload_evidence_for_other_teacher(): void
    {
        $otherTeacherProfile = TeacherProfile::factory()->create();
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $assignment = KpiFormAssignment::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $item = KpiFormItem::factory()->create();

        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $otherTeacherProfile->id,
            'assessment_period_id' => $period->id,
            'assignment_id' => $assignment->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [$assessment, $item]), [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_cannot_upload_evidence_for_submitted_assessment(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $assignment = KpiFormAssignment::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $item = KpiFormItem::factory()->create();

        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'assignment_id' => $assignment->id,
            'status' => 'submitted',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.evidence.upload', [$assessment, $item]), [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_delete_own_evidence(): void
    {
        $assessmentItemValue = AssessmentItemValue::factory()->create();
        $evidence = EvidenceUpload::factory()->create([
            'assessment_item_value_id' => $assessmentItemValue->id,
            'uploaded_by' => $this->teacher->id,
            'path' => 'evidence/test.pdf',
        ]);

        // Mock the period as active
        $assessmentItemValue->assessment->update(['status' => 'draft']);
        $assessmentItemValue->assessment->period->update(['status' => 'active']);

        $response = $this->actingAs($this->teacher)
            ->delete(route('teacher.evidence.destroy', $evidence));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function teacher_cannot_delete_other_teacher_evidence(): void
    {
        $assessmentItemValue = AssessmentItemValue::factory()->create();
        $evidence = EvidenceUpload::factory()->create([
            'assessment_item_value_id' => $assessmentItemValue->id,
            'uploaded_by' => User::factory()->create()->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->delete(route('teacher.evidence.destroy', $evidence));

        $response->assertStatus(403);
    }

    /** @test */
    public function non_teacher_cannot_access_evidence(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.evidence.index'));

        $response->assertStatus(403);
    }
}
