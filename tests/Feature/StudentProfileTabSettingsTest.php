<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentProfileTabSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileTabSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_profile_tabs_render_with_default_order_when_settings_do_not_exist(): void
    {
        $student = Student::factory()->create([
            'exam_score' => 95,
            'ranking' => '1',
            'pcro_remarks' => 'Complete requirements',
        ]);

        $html = $this->renderStudentProfile($student);

        $this->assertDatabaseCount((new StudentProfileTabSetting)->getTable(), 0);
        $this->assertStringContainsString('Student Profile', $html);
        $this->assertStringContainsString($student->fullname, $html);
        $this->assertStringContainsString('Details', $html);
        $this->assertStringContainsString('Assessment', $html);
        $this->assertStringContainsString('Remarks', $html);
        $this->assertLessThan(
            strpos($html, 'Assessment'),
            strpos($html, 'Details'),
        );
    }

    public function test_hidden_student_profile_tab_is_not_rendered(): void
    {
        $student = Student::factory()->create([
            'pcro_remarks' => 'Complete requirements',
        ]);

        StudentProfileTabSetting::query()->create([
            'tab_key' => 'remarks',
            'tab_name' => 'Remarks',
            'is_visible' => false,
            'sort_order' => 30,
        ]);

        $html = $this->renderStudentProfile($student);

        $this->assertStringNotContainsString('Remarks', $html);
        $this->assertStringNotContainsString('Complete requirements', $html);
        $this->assertStringContainsString('Details', $html);
    }

    public function test_student_profile_tabs_follow_saved_order(): void
    {
        $student = Student::factory()->create([
            'exam_score' => 95,
            'ranking' => '1',
        ]);

        StudentProfileTabSetting::query()->create([
            'tab_key' => 'assessment',
            'tab_name' => 'Assessment',
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $html = $this->renderStudentProfile($student);

        $this->assertLessThan(
            strpos($html, 'Details'),
            strpos($html, 'Assessment'),
        );
    }

    private function renderStudentProfile(Student $student): string
    {
        return view('filament.resources.students.actions.view-student', [
            'record' => $student,
        ])->render();
    }
}
