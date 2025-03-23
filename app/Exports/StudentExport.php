<?php

namespace App\Exports;

use App\Models\Student;
use App\Traits\HasAverage;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Exports\Sheets\StudentCategorySheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentExport implements WithMultipleSheets
{

    use HasAverage;

    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    

    // public function collection()
    // {
    //     return Student::with('scores')
    //             // ->where('id', 2410)
    //             // ->limit(10)
    //             ->get()
    //             ->map(fn($item) => [
    //                 'Name' => $item->fullname,
    //                 'Municipal' => $item->municipality,
    //                 'Category' => $item->type,
    //                 // 'Exam_Score' => $item->exam_score,
    //                 // 'Total' => $item->scores->sum('totalScore'),
    //                 // 'Average' => $item->scores->average('totalScore'),
    //                 // 'id' => $item->id,
    //                 'pcro_remarks' => $item->pcro_remarks,
    //                 'Panel_Remarks' => $item->scores->map(fn($row, $key) => ['remarks' => $row->remarks ? "- ".str_replace("\n",'',$row->remarks)."\n": ''])->implode('remarks'),
    //                 'Exam_Score' => ($item->exam_score * 0.5),
    //                 'Total' => $item->scores->average('totalScore') * 0.5,
    //                 'total_average' => ($item->exam_score*0.5) + ($item->scores->average('totalScore') * 0.5),
    //             ]);
    //     // return Student::select('fullname')->get();
    // }

    public function sheets(): array
    {
            $sheets =Student::with('scores')
                        // ->where('id', 2410)
                        // ->limit(10)
                        // ->whereNot('type', '')
                        ->get()
                        ->groupBy('type')
                        // ->dd()
                        ->map(function($row, $key) {
                            if (!$key) {
                                $key = 'others';
                            }
                            $data = $row->map(fn($item) => [
                                'Name' => $item->fullname,
                                'Municipal' => $item->municipality,
                                'Category' => $item->type,
                                // 'Exam_Score' => $item->exam_score,
                                // 'Total' => $item->scores->sum('totalScore'),
                                // 'Average' => $item->scores->average('totalScore'),
                                // 'id' => $item->id,
                                'pcro_remarks' => $item->pcro_remarks,
                                'Panel_Remarks' => $item->scores->map(fn($row, $key) => ['remarks' => $row->remarks ? "- ".str_replace("\n",'',$row->remarks)."\n": ''])->implode('remarks'),
                                'Exam_Score' => ($item->exam_score * 0.5),
                                'Total' => $item->scores->average('totalScore') * 0.5,
                                'total_average' => ($item->exam_score*0.5) + ($item->scores->average('totalScore') * 0.5) == 0 ? '0' : ($item->exam_score*0.5) + ($item->scores->average('totalScore') * 0.5),
                            ]);
                            return new StudentCategorySheet($data, $key);
                        });

        return $sheets->toArray();
    }
    
}
