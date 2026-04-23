<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('fullname')
                ->formatStateUsing(fn (?string $state): ?string => self::safeSpreadsheetText($state)),
            ExportColumn::make('municipality')
                ->formatStateUsing(fn (?string $state): ?string => self::safeSpreadsheetText($state)),
            ExportColumn::make('type')
                ->formatStateUsing(fn (?string $state): ?string => self::safeSpreadsheetText($state)),
            ExportColumn::make('exam_score'),
            ExportColumn::make('pcro_remarks')
                ->formatStateUsing(fn (?string $state): ?string => self::safeSpreadsheetText($state)),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    private static function safeSpreadsheetText(?string $value): ?string
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
