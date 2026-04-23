<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    public function collection(Collection $collection): void
    {
        $now = now();

        $data = $collection
            ->filter(fn (Collection $row): bool => $row->filter()->isNotEmpty())
            ->map(fn (Collection $row): array => [
                'last_name' => trim((string) ($row[1] ?? '')),
                'first_name' => trim((string) ($row[2] ?? '')),
                'municipality' => blank($row[3] ?? null) ? null : trim((string) $row[3]),
                'type' => blank($row[4] ?? null) ? null : trim((string) $row[4]),
                'fullname' => trim(((string) ($row[2] ?? '')).' '.((string) ($row[1] ?? ''))),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values();

        Validator::make($data->all(), [
            '*.last_name' => ['required', 'string', 'max:255'],
            '*.first_name' => ['required', 'string', 'max:255'],
            '*.municipality' => ['nullable', 'string', 'max:255'],
            '*.type' => ['nullable', 'string', 'max:255'],
            '*.fullname' => ['required', 'string', 'max:255', 'distinct'],
        ])->validate();

        DB::transaction(function () use ($data): void {
            $data->chunk(100)->each(function (Collection $rows): void {
                DB::table('students')->insert($rows->all());
            });
        });
    }
}
