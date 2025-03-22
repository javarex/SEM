<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentExamImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        DB::beginTransaction();
        try {
            $data = $collection->map(function ($row) {
                return [
                    'last_name' => $row[0],
                    'first_name' => $row[1],
                    'exam_score' => $row[2],
                    'pcro_remarks' => $row[3],
                    'fullname' => $row[1].' '.$row[0],
                ];
            });

            DB::table('students')->upsert($data->toArray(), ['fullname'],  ['exam_score', 'pcro_remarks']);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
        }

    }
}
