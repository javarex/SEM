<?php

namespace App\Filament\Widgets;

use App\Models\StudentScore;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class DailyInterviewsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.daily-interviews-widget';

    protected int|string|array $columnSpan = 'full';

    public ?string $selectedDate = null;

    public bool $isModalOpen = false;

    /**
     * @return array<int, array{date: string, label: string, count: int}>
     */
    public function getDateCards(): array
    {
        return StudentScore::get()
            ->each(function (StudentScore $score): void {
                $score->date = $score->created_at->format('Y-m-d');
            })
            ->groupBy('date')
            ->sortKeysDesc()
            ->map(fn (Collection $scores, string $date): array => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('F j, Y'),
                'count' => $scores->groupBy('student_id')->count(),
            ])
            ->values()
            ->all();
    }

    public function showInterviewedStudents(string $date): void
    {
        abort_unless($this->dateExists($date), 404);

        $this->selectedDate = $date;
        $this->isModalOpen = true;
    }

    public function closeInterviewedStudentsModal(): void
    {
        $this->isModalOpen = false;
        $this->selectedDate = null;
    }

    public function getSelectedDateLabel(): ?string
    {
        return $this->selectedDate
            ? Carbon::parse($this->selectedDate)->format('F j, Y')
            : null;
    }

    public function getSelectedInterviewScores(): Collection
    {
        if (! $this->selectedDate) {
            return collect();
        }

        return StudentScore::query()
            ->with('student')
            ->whereDate('created_at', $this->selectedDate)
            ->latest('id')
            ->get()
            ->unique('student_id')
            ->values();
    }

    protected function dateExists(string $date): bool
    {
        return StudentScore::query()
            ->whereDate('created_at', $date)
            ->exists();
    }
}
