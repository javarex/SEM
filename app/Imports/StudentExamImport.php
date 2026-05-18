<?php

namespace App\Imports;

use Carbon\CarbonInterface;
use EightyNine\ExcelImport\Exceptions\ImportStoppedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentExamImport implements ToCollection
{
    public function collection(Collection $collection): void
    {
        $now = now();

        $rows = $collection
            ->filter(fn (Collection $row): bool => $row->filter()->isNotEmpty())
            ->values();

        $data = $this->isPanelInterviewWorkbook($rows)
            ? $this->mapPanelInterviewRows($rows, $now)
            : $this->mapExamRows($rows, $now);

        if ($data->isEmpty()) {
            throw new ImportStoppedException('No student rows were found in the uploaded Excel file.', 'error');
        }

        try {
            Validator::make($data->all(), [
                '*.fullname' => ['required', 'string', 'max:255', 'distinct'],
                '*.sex' => ['nullable', 'string', 'max:255'],
                '*.purok' => ['nullable', 'string', 'max:255'],
                '*.barangay' => ['nullable', 'string', 'max:255'],
                '*.municipality' => ['nullable', 'string', 'max:255'],
                '*.school' => ['nullable', 'string', 'max:255'],
                '*.family_background' => ['nullable', 'string', 'max:255'],
                '*.category' => ['nullable', 'string', 'max:255'],
                '*.ethnicity' => ['nullable', 'string', 'max:255'],
                '*.type' => ['nullable', 'string', 'max:255'],
                '*.ranking' => ['nullable', 'string', 'max:255'],
                '*.exam_score' => ['nullable', 'numeric', 'min:0'],
                '*.pcro_remarks' => ['nullable', 'string', 'max:255'],
                '*.cao_remarks' => ['nullable', 'string'],
                '*.ydd_remarks' => ['nullable', 'string'],
            ], [
                '*.fullname.required' => 'Every imported student must have a full name.',
                '*.fullname.distinct' => 'The uploaded file contains duplicate student names.',
            ])->validate();
        } catch (ValidationException $exception) {
            throw new ImportStoppedException(
                'Student import failed: '.$exception->validator->errors()->first(),
                'error',
            );
        }

        DB::transaction(function () use ($data): void {
            DB::table('students')->upsert(
                $data->all(),
                ['fullname'],
                [
                    'first_name',
                    'middle_name',
                    'last_name',
                    'sex',
                    'purok',
                    'barangay',
                    'municipality',
                    'school',
                    'family_background',
                    'category',
                    'ethnicity',
                    'type',
                    'ranking',
                    'exam_score',
                    'pcro_remarks',
                    'cao_remarks',
                    'ydd_remarks',
                    'updated_at',
                ],
            );
        });
    }

    private function isPanelInterviewWorkbook(Collection $rows): bool
    {
        return $rows->contains(function (Collection $row): bool {
            return str($row[2] ?? '')->trim()->lower()->is('name')
                && str($row[3] ?? '')->trim()->lower()->is('sex');
        });
    }

    private function mapExamRows(Collection $rows, CarbonInterface $now): Collection
    {
        return $rows
            ->map(fn (Collection $row): array => [
                'last_name' => null,
                'middle_name' => null,
                'first_name' => null,
                'sex' => null,
                'purok' => null,
                'barangay' => null,
                'municipality' => null,
                'school' => null,
                'family_background' => null,
                'category' => null,
                'ethnicity' => null,
                'type' => null,
                'ranking' => null,
                'exam_score' => blank($row[2] ?? null) ? null : $row[2],
                'pcro_remarks' => blank($row[3] ?? null) ? null : trim((string) $row[3]),
                'fullname' => trim(((string) ($row[1] ?? '')).' '.((string) ($row[0] ?? ''))),
                'cao_remarks' => null,
                'ydd_remarks' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();
    }

    private function mapPanelInterviewRows(Collection $rows, CarbonInterface $now): Collection
    {
        return $rows
            ->filter(fn (Collection $row): bool => filled($row[2] ?? null) && is_numeric($row[0] ?? null))
            ->map(function (Collection $row) use ($now): array {
                return [
                    'last_name' => null,
                    'middle_name' => null,
                    'first_name' => null,
                    'sex' => $this->nullableString($row[3] ?? null),
                    'purok' => $this->nullableString($row[4] ?? null),
                    'barangay' => $this->nullableString($row[5] ?? null),
                    'municipality' => blank($row[6] ?? null) ? null : trim((string) $row[6]),
                    'school' => $this->nullableString($row[7] ?? null),
                    'family_background' => $this->nullableString($row[8] ?? null),
                    'category' => $this->panelInterviewCategory($row),
                    'ethnicity' => $this->nullableString($row[12] ?? null),
                    'type' => $this->nullableString($row[13] ?? null),
                    'ranking' => $this->nullableString($row[15] ?? null),
                    'exam_score' => blank($row[14] ?? null) ? null : $row[14],
                    'pcro_remarks' => null,
                    'fullname' => $this->panelInterviewFullname($row[2] ?? null),
                    'cao_remarks' => $this->nullableString($row[18] ?? null),
                    'ydd_remarks' => $this->nullableString($row[19] ?? null),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->values();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = str($value)->squish()->toString();

        return $value === '' ? null : $value;
    }

    private function panelInterviewFullname(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null || ! str_contains($value, ',')) {
            return $value;
        }

        [$lastName, $givenNames] = array_pad(explode(',', $value, 2), 2, '');

        return $this->nullableString(trim($givenNames).' '.trim($lastName));
    }

    private function panelInterviewCategory(Collection $row): ?string
    {
        $types = collect([
            $row[9] ?? null,
            $row[10] ?? null,
            $row[11] ?? null,
            $row[16] ?? null,
        ])
            ->map(fn ($value): ?string => $this->nullableString($value))
            ->filter()
            ->values();

        return $types->isEmpty() ? null : $types->implode(', ');
    }
}
