<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentCategorySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  Collection<int, array<string, mixed>>  $collection
     */
    public function __construct(
        protected Collection $collection,
        protected string $key,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(): Collection
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

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return str($this->key ?: 'others')
            ->replace(['\\', '/', '?', '*', '[', ']', ':'], '-')
            ->limit(31, '')
            ->toString();
    }
}
