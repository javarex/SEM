<?php

namespace Tests\Feature;

use App\Exports\StudentExport;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Database\Eloquent\Collection;
use ReflectionMethod;
use Tests\TestCase;

class StudentExportTest extends TestCase
{
    public function test_student_export_preserves_decimal_scores(): void
    {
        $student = new Student([
            'fullname' => 'Test Student',
            'exam_score' => 89.5,
            'type' => 'Academic',
        ]);

        $student->setRelation('scores', new Collection([
            new StudentScore([
                'emotional' => 3.5,
                'intelligence' => 4,
                'socio_economic' => 3,
            ]),
            new StudentScore([
                'emotional' => 8,
                'intelligence' => 7.5,
                'socio_economic' => 7.5,
            ]),
        ]));

        $mapStudent = new ReflectionMethod(StudentExport::class, 'mapStudent');
        $exportedStudent = $mapStudent->invoke(new StudentExport('all'), $student);

        $this->assertSame(89.5, $exportedStudent['written_exam_score_raw']);
        $this->assertSame(16.75, $exportedStudent['panel_interview_score_average']);
        $this->assertSame(44.75, $exportedStudent['written_exam_score_weighted']);
        $this->assertSame(16.75, $exportedStudent['panel_interview_score_weighted']);
        $this->assertSame(61.5, $exportedStudent['overall_score']);
    }
}
