<?php

namespace App\Exports;

use App\Exports\Sheets\StudentCategorySheet;
use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly string $municipality,
    ) {}

    /**
     * @return array<int, StudentCategorySheet>
     */
    public function sheets(): array
    {
        return Student::query()
            ->with(['scores' => fn ($query) => $query->orderBy('id')])
            ->when(
                $this->municipality !== 'all',
                fn ($query) => $query->where('municipality', $this->municipality)
            )
            ->get()
            ->groupBy(fn (Student $student): string => $student->type ?: 'others')
            ->map(function (Collection $students, string $key): StudentCategorySheet {
                $rankedStudents = $students
                    ->map(fn (Student $student): array => $this->mapStudent($student))
                    ->sortByDesc('overall_score')
                    ->values()
                    ->map(function (array $student, int $index): array {
                        $student['rank'] = $index + 1;

                        return $student;
                    });

                return new StudentCategorySheet($rankedStudents, $key);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapStudent(Student $student): array
    {
        $scores = $student->scores->values();
        $panelAverage = (float) ($scores->average('totalScore') ?? 0);
        $examScore = (float) ($student->exam_score ?? 0);
        $examScoreWeighted = $examScore * 0.5;
        $panelScoreWeighted = $panelAverage;
        $overallScore = $examScoreWeighted + $panelScoreWeighted;

        return [
            'name' => $this->safeSpreadsheetText($student->fullname),
            'sex' => $this->safeSpreadsheetText($student->sex),
            'municipality' => $this->safeSpreadsheetText($student->municipality),
            'barangay' => $this->safeSpreadsheetText($student->barangay),
            'purok' => $this->safeSpreadsheetText($student->purok),
            'school' => $this->safeSpreadsheetText($student->school),
            'family_background' => $this->safeSpreadsheetText($student->family_background),
            'ethnicity' => $this->safeSpreadsheetText($student->ethnicity),
            'scholarship_type' => $this->safeSpreadsheetText($student->type),
            'ydd_remarks' => $this->safeSpreadsheetText($student->ydd_remarks),
            'pcro_remarks' => $this->safeSpreadsheetText($student->cao_remarks),
            'panel_remarks' => $this->safeSpreadsheetText($this->panelRemarks($scores)),
            'category' => $this->safeSpreadsheetText($student->category),
            'written_exam_score_raw' => $this->scoreValue($examScore),
            'panel_interview_score_average' => $this->scoreValue($panelAverage),
            'written_exam_score_weighted' => $this->scoreValue($examScoreWeighted),
            'panel_interview_score_weighted' => $this->scoreValue($panelScoreWeighted),
            'overall_score' => $this->scoreValue($overallScore),
            'rank' => null,
        ];
    }

    private function scoreValue(float|int|null $score): float|int|null
    {
        if ($score === null) {
            return null;
        }

        return $score;
    }

    private function panelRemarks(Collection $scores): ?string
    {
        $remarks = $scores
            ->pluck('remarks')
            ->map(function (?string $remark): ?string {
                if ($remark === null) {
                    return null;
                }

                return trim((string) preg_replace('/\s+/', ' ', $remark));
            })
            ->filter()
            ->values();

        if ($remarks->isEmpty()) {
            return null;
        }

        return $remarks
            ->map(fn (string $remark, int $index): string => ($index + 1).'. '.$remark)
            ->implode("\n");
    }

    private function safeSpreadsheetText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmedValue = ltrim($value);

        if ($trimmedValue !== '' && in_array($trimmedValue[0], ['=', '+', '-', '@'], true)) {
            return "'{$value}";
        }

        return $value;
    }
}
