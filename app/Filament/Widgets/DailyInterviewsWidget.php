<?php

namespace App\Filament\Widgets;

use App\Models\StudentScore;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Throwable;

class DailyInterviewsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.daily-interviews-widget';

    protected int|string|array $columnSpan = 'full';

    #[Locked]
    public ?string $selectedDate = null;

    public bool $isModalOpen = false;

    /**
     * @return array<int, array{date: string, label: string, count: int}>
     */
    public function getDateCards(): array
    {
        $this->authorizeViewing();

        return StudentScore::query()
            ->selectRaw('DATE(created_at) as interview_date, COUNT(DISTINCT student_id) as students_count')
            ->whereNotNull('created_at')
            ->groupByRaw('DATE(created_at)')
            ->orderByDesc('interview_date')
            ->get()
            ->map(fn (StudentScore $score): array => [
                'date' => (string) $score->interview_date,
                'label' => Carbon::parse($score->interview_date)->format('F j, Y'),
                'count' => (int) $score->students_count,
            ])
            ->all();
    }

    public function showInterviewedStudents(string $date): void
    {
        $this->authorizeViewing();

        $normalizedDate = $this->normalizeDate($date);

        abort_unless($normalizedDate !== null && $this->dateExists($normalizedDate), 404);

        $this->selectedDate = $normalizedDate;
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
        if (! static::canView() || ! $this->selectedDate || ! $this->dateExists($this->selectedDate)) {
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

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) (
            $user?->can('View:DailyInterviewsWidget')
            || $user?->can('View:StudentInterviewedWidget')
            || $user?->hasRole('super_admin')
        );
    }

    protected function authorizeViewing(): void
    {
        abort_unless(static::canView(), 403);
    }

    protected function dateExists(string $date): bool
    {
        return StudentScore::query()
            ->whereNotNull('created_at')
            ->whereDate('created_at', $date)
            ->exists();
    }

    protected function normalizeDate(string $date): ?string
    {
        try {
            return Carbon::parse($date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
