<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentCategorySheet implements FromCollection, WithColumnWidths, WithHeadings, WithStyles, WithTitle
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
            'sex',
            'Municipality',
            'barangay',
            'purok',
            'school',
            'family background',
            'ethnicity',
            'scholarship type',
            'ydd remarks',
            'PCRO Remarks',
            'Panel Remarks',
            'Category',
            'written exam score (raw)',
            'panel interview score (average)',
            'written exam score (equivalent to 50%)',
            'panel interview score (equivalent to 50%)',
            'overall score',
            'rank',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:S{$highestRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * @return array<string, float>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30.7,
            'B' => 30.7,
            'C' => 21.7,
            'D' => 21.7,
            'E' => 21.7,
            'F' => 21.7,
            'G' => 11.4,
            'H' => 14.6,
            'I' => 14.6,
            'J' => 17.1,
            'K' => 24,
            'L' => 11.4,
            'M' => 12.1,
            'N' => 18,
            'O' => 18,
            'P' => 18,
            'Q' => 14,
            'R' => 10,
            'S' => 10,
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
