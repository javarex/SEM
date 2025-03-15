<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        DB::beginTransaction();
        try {
            $data = $collection->map(fn ($row, $key) => [
                'last_name' => $row[1],
                'first_name' => $row[2],
                'municipality' => $row[3],
                'type' => $row[4],
            ]);

            $data->chunk(100)->each(function ($row, $key) {
                DB::table('students')->insert($row->toArray());
            });

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
        }
    }
}
