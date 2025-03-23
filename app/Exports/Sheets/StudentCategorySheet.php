<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentCategorySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $collection;
    protected $key;

    public function __construct(Collection $collection, $key)
    {
        $this->collection = $collection;
        // dump($key);
        $this->key = $key;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'Name',
            'Municipality',
            'Category',
            // 'Exam Score',
            // 'Total',
            // 'id',
            'PCRO Remarks',
            'Panel Remarks',
            'Exam Score (.5)',
            'Panel Score(.5)',
            'Average',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]]
        ];
    }


    public function title(): string
    {
        // dd($this->key);
        try {
            return "{$this->key}";
            //code...
        } catch (\Throwable $th) {
            dd($this->key);
        }
    }
}
