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

    /**
     * @return array<int, StudentCategorySheet>
     */
    public function sheets(): array
    {
        return Student::query()
            ->with('scores')
            ->get()
            ->groupBy(fn (Student $student): string => $student->type ?: 'others')
            ->map(function (Collection $students, string $key): StudentCategorySheet {
                $data = $students->map(function (Student $student): array {
                    $panelScore = (float) ($student->scores->average('totalScore') ?? 0) * 0.5;
                    $examScore = (float) ($student->exam_score ?? 0) * 0.5;
                    $totalAverage = $examScore + $panelScore;

                    return [
                        'Name' => $this->safeSpreadsheetText($student->fullname),
                        'Municipal' => $this->safeSpreadsheetText($student->municipality),
                        'Category' => $this->safeSpreadsheetText($student->type),
                        'pcro_remarks' => $this->safeSpreadsheetText($student->pcro_remarks),
                        'Panel_Remarks' => $this->safeSpreadsheetText(
                            $student->scores
                                ->map(fn ($score): ?string => $score->remarks ? '- '.str_replace("\n", '', $score->remarks) : null)
                                ->filter()
                                ->implode("\n")
                        ),
                        'Exam_Score' => $examScore,
                        'Total' => $panelScore,
                        'total_average' => $totalAverage === 0.0 ? '0' : $totalAverage,
                    ];
                });

                return new StudentCategorySheet($data, $key);
            })
            ->values()
            ->all();
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
