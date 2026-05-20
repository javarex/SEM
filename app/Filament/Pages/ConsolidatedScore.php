<?php

namespace App\Filament\Pages;

use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Override;

class ConsolidatedScore extends Page
{
    use HasPageShield;
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.consolidated-score';

    public ?string $team = null;

    public int $perPage = 10;

    public function updatedTeam(): void
    {
        $this->resetPage();
    }

    #[Override]
    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'judges' => $this->getJudges(),
            'students' => $this->getPaginatedStudents(),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, team: ?string}>
     */
    protected function getJudges(): array
    {
        return User::query()
            ->whereHas('studentScores')
            ->when($this->team, fn ($query, string $team) => $query->where('team', $team))
            ->orderBy('name')
            ->get(['id', 'name', 'team'])
            ->map(fn (User $judge): array => [
                'id' => $judge->id,
                'name' => $judge->name,
                'team' => $judge->team?->value,
            ])
            ->values()
            ->all();
    }

    protected function getPaginatedStudents(): LengthAwarePaginator
    {
        $students = DB::query()
            ->fromSub($this->studentScoreSummaryQuery(), 'student_score_summary')
            ->orderByDesc('average_score')
            ->orderBy('student_name')
            ->paginate($this->perPage);

        $studentIds = $students->getCollection()
            ->pluck('student_id')
            ->all();

        $grades = $this->getGradesForStudents($studentIds);
        $firstRank = $students->firstItem() ?? 1;

        $students->setCollection(
            $students->getCollection()
                ->values()
                ->map(function ($student, int $index) use ($grades, $firstRank): array {
                    $averageScore = (float) $student->average_score;
                    $examScore = (float) ($student->exam_score ?? 0);

                    return [
                        'id' => (int) $student->student_id,
                        'name' => $student->student_name,
                        'examScore' => $examScore,
                        'grades' => $grades->get((int) $student->student_id, []),
                        'averageScore' => $averageScore,
                        'examScoreWeighted' => $examScore * 0.5,
                        'panelScoreWeighted' => $averageScore * 0.5,
                        'finalAverage' => ($examScore * 0.5) + ($averageScore * 0.5),
                        'rank' => $firstRank + $index,
                    ];
                })
        );

        return $students;
    }

    protected function studentScoreSummaryQuery(): Builder
    {
        return DB::query()
            ->fromSub($this->judgeScoreAveragesQuery(), 'judge_score_averages')
            ->join('students', 'students.id', '=', 'judge_score_averages.student_id')
            ->selectRaw('
                students.id as student_id,
                students.fullname as student_name,
                students.exam_score,
                AVG(judge_score_averages.judge_total) as average_score
            ')
            ->groupBy('students.id', 'students.fullname', 'students.exam_score');
    }

    protected function judgeScoreAveragesQuery(): Builder
    {
        return DB::table('student_scores')
            ->join('users as judges', 'student_scores.user_id', '=', 'judges.id')
            ->whereNull('student_scores.deleted_at')
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('judges.team', $team))
            ->selectRaw('
                student_scores.student_id,
                judges.id as judge_id,
                judges.name as judge_name,
                judges.team as judge_team,
                AVG(student_scores.emotional) as avg_emotional,
                AVG(student_scores.intelligence) as avg_intelligence,
                AVG(student_scores.socio_economic) as avg_socio_economic,
                AVG(student_scores.emotional) + AVG(student_scores.intelligence) + AVG(student_scores.socio_economic) as judge_total
            ')
            ->groupBy('student_scores.student_id', 'judges.id', 'judges.name', 'judges.team');
    }

    /**
     * @param  array<int, int>  $studentIds
     * @return Collection<int, array<int, array{emotional: float, intelligence: float, socio_economic: float}>>
     */
    protected function getGradesForStudents(array $studentIds): Collection
    {
        if ($studentIds === []) {
            return collect();
        }

        return DB::query()
            ->fromSub($this->judgeScoreAveragesQuery(), 'judge_score_averages')
            ->whereIn('student_id', $studentIds)
            ->orderBy('judge_name')
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $studentScores): array => $studentScores
                ->mapWithKeys(fn ($score): array => [
                    (int) $score->judge_id => [
                        'emotional' => (float) $score->avg_emotional,
                        'intelligence' => (float) $score->avg_intelligence,
                        'socio_economic' => (float) $score->avg_socio_economic,
                    ],
                ])
                ->all());
    }
}
