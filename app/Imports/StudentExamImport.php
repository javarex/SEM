<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentExamImport implements ToCollection
{
    public function collection(Collection $collection): void
    {
        $now = now();

        $data = $collection
            ->filter(fn (Collection $row): bool => $row->filter()->isNotEmpty())
            ->map(fn (Collection $row): array => [
                'last_name' => trim((string) ($row[0] ?? '')),
                'first_name' => trim((string) ($row[1] ?? '')),
                'exam_score' => blank($row[2] ?? null) ? null : $row[2],
                'pcro_remarks' => blank($row[3] ?? null) ? null : trim((string) $row[3]),
                'fullname' => trim(((string) ($row[1] ?? '')).' '.((string) ($row[0] ?? ''))),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();

        Validator::make($data->all(), [
            '*.last_name' => ['required', 'string', 'max:255'],
            '*.first_name' => ['required', 'string', 'max:255'],
            '*.exam_score' => ['nullable', 'numeric', 'min:0'],
            '*.pcro_remarks' => ['nullable', 'string', 'max:255'],
            '*.fullname' => ['required', 'string', 'max:255', 'distinct'],
        ])->validate();

        DB::transaction(function () use ($data): void {
            DB::table('students')->upsert(
                $data->all(),
                ['fullname'],
                ['exam_score', 'pcro_remarks', 'updated_at'],
            );
        });
    }
}
