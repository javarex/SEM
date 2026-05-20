<?php

namespace App\Filament\Pages;

use App\Exports\PanelInterviewMonitoringReportExport;
use App\Models\Student;
use App\Models\User;
use App\UserTeam;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PanelInterviewMonitoringReport extends Page
{
    use HasPageShield;
    use WithPagination;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Panelist Interview Monitoring';

    protected static ?string $title = 'Panelist Interview Monitoring Report';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.panel-interview-monitoring-report';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public ?string $team = null;

    public ?string $panelistId = null;

    public ?string $municipality = null;

    public string $status = 'all';

    public ?string $selectedRatingStatus = null;

    public int $perPage = 10;

    protected const STUDENT_SAMPLE_LIMIT = 8;

    protected const DRILL_DOWN_LIMIT = 250;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) (
            $user?->hasRole('super_admin')
            || $user?->can('View:PanelInterviewMonitoringReport')
        );
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['startDate', 'endDate', 'team', 'panelistId', 'municipality', 'status'], true)) {
            $this->resetPage('ratedPage');
            $this->resetPage('unratedPage');
            $this->selectedRatingStatus = null;
        }
    }

    public function showRatingStatus(string $status): void
    {
        abort_unless(in_array($status, ['complete', 'incomplete', 'invalid_mixed_team', 'not_rated'], true), 404);

        $this->selectedRatingStatus = $status;
    }

    public function closeRatingStatus(): void
    {
        $this->selectedRatingStatus = null;
    }

    public function export(): BinaryFileResponse
    {
        abort_unless(static::canAccess(), 403);

        return Excel::download(
            new PanelInterviewMonitoringReportExport($this->filters()),
            now()->format('Y-m-d-His').'-panel-interview-monitoring-report.xlsx'
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'panelists' => $this->panelistOptions(),
            'municipalities' => $this->municipalityOptions(),
            'teamOptions' => UserTeam::options(),
            'ratedRows' => $this->status !== 'unrated' ? $this->ratedRows() : null,
            'unratedRows' => $this->status !== 'rated' ? $this->unratedRows() : null,
            'teamCoverageRows' => $this->teamCoverageRows(),
            'ratingStatusCards' => $this->ratingStatusCards(),
            'selectedRatingStatusLabel' => $this->selectedRatingStatusLabel(),
            'selectedRatingStatusRows' => $this->selectedRatingStatusRows(),
            'totalStudents' => $this->filteredStudentCount(),
            'showRated' => $this->status !== 'unrated',
            'showUnrated' => $this->status !== 'rated',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'team' => $this->team,
            'panelistId' => $this->panelistId,
            'municipality' => $this->municipality,
            'status' => $this->status,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function panelistOptions(): array
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'panelist'))
            ->whereNotNull('team')
            ->where('team', '!=', '')
            ->when($this->team, fn ($query, string $team) => $query->where('team', $team))
            ->orderBy('team')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function municipalityOptions(): array
    {
        return Student::query()
            ->whereNotNull('municipality')
            ->where('municipality', '!=', '')
            ->distinct()
            ->orderBy('municipality')
            ->pluck('municipality', 'municipality')
            ->all();
    }

    protected function filteredStudentCount(): int
    {
        return $this->filteredStudentsQuery()->count();
    }

    protected function ratedRows(): LengthAwarePaginator
    {
        $rows = $this->ratedRowsQuery()
            ->paginate($this->perPage, pageName: 'ratedPage');

        $rows->setCollection(
            $rows->getCollection()
                ->map(function ($row) {
                    $row->rated_students = $this->ratedStudentSamples((int) $row->panelist_id, (string) $row->interview_date);

                    return $row;
                })
        );

        return $rows;
    }

    protected function unratedRows(): LengthAwarePaginator
    {
        $rows = $this->unratedRowsQuery()
            ->paginate($this->perPage, pageName: 'unratedPage');

        $rows->setCollection(
            $rows->getCollection()
                ->map(function ($row) {
                    $row->unrated_students = $this->unratedStudentSamples((int) $row->panelist_id);

                    return $row;
                })
        );

        return $rows;
    }

    protected function teamCoverageRows(): Collection
    {
        $totalStudents = $this->filteredStudentCount();

        return $this->teamsForCoverage()
            ->map(function (?string $team) use ($totalStudents): array {
                $ratedStudents = $this->teamRatedStudentCount((string) $team);
                $unratedStudents = max($totalStudents - $ratedStudents, 0);

                return [
                    'team' => $team,
                    'team_label' => UserTeam::tryFrom((string) $team)?->getLabel() ?? 'No Team',
                    'rated_students' => $ratedStudents,
                    'unrated_students' => $unratedStudents,
                    'unrated_student_names' => $this->teamUnratedStudentSamples((string) $team),
                    'completion_percentage' => $totalStudents > 0
                        ? round(($ratedStudents / $totalStudents) * 100, 2)
                        : 0.0,
                ];
            });
    }

    /**
     * @return array<string, array{label: string, description: string, count: int, color: string}>
     */
    protected function ratingStatusCards(): array
    {
        return [
            'complete' => [
                'label' => 'Complete Rating',
                'description' => 'Exactly 3 panelists from one team',
                'count' => $this->ratingStatusCount('complete'),
                'color' => 'success',
            ],
            'incomplete' => [
                'label' => 'Incomplete Rating',
                'description' => 'Rated by 1 or 2 panelists',
                'count' => $this->ratingStatusCount('incomplete'),
                'color' => 'warning',
            ],
            'invalid_mixed_team' => [
                'label' => 'Invalid - Mixed Team',
                'description' => 'Panelists came from multiple teams',
                'count' => $this->ratingStatusCount('invalid_mixed_team'),
                'color' => 'danger',
            ],
            'not_rated' => [
                'label' => 'Not Rated',
                'description' => 'No matching rating records',
                'count' => $this->notRatedStudentsQuery()->count(),
                'color' => 'gray',
            ],
        ];
    }

    protected function ratingStatusCount(string $status): int
    {
        return DB::query()
            ->fromSub($this->studentRatingSummaryQuery(), 'rating_summary')
            ->tap(fn (Builder $query) => $this->applyRatingStatusConstraint($query, $status))
            ->count();
    }

    protected function selectedRatingStatusLabel(): ?string
    {
        return match ($this->selectedRatingStatus) {
            'complete' => 'Complete Rating',
            'incomplete' => 'Incomplete Rating',
            'invalid_mixed_team' => 'Invalid - Mixed Team',
            'not_rated' => 'Not Rated',
            default => null,
        };
    }

    /**
     * @return Collection<int, object>
     */
    protected function selectedRatingStatusRows(): Collection
    {
        return match ($this->selectedRatingStatus) {
            'complete', 'incomplete', 'invalid_mixed_team' => $this->ratedStatusRows($this->selectedRatingStatus),
            'not_rated' => $this->notRatedStatusRows(),
            default => collect(),
        };
    }

    protected function ratedStatusRows(string $status): Collection
    {
        return DB::query()
            ->fromSub($this->studentRatingSummaryQuery(), 'rating_summary')
            ->join('students', 'students.id', '=', 'rating_summary.student_id')
            ->leftJoinSub($this->studentRatingDetailsQuery(), 'rating_details', 'rating_details.student_id', '=', 'rating_summary.student_id')
            ->tap(fn (Builder $query) => $this->applyRatingStatusConstraint($query, $status))
            ->select([
                'students.id',
                'students.fullname',
                'students.municipality',
                'rating_summary.panelist_count',
                'rating_summary.team_count',
                'rating_details.teams',
                'rating_details.panelists',
                'rating_details.rating_dates',
            ])
            ->orderBy('students.fullname')
            ->limit(self::DRILL_DOWN_LIMIT)
            ->get();
    }

    protected function notRatedStatusRows(): Collection
    {
        return $this->notRatedStudentsQuery()
            ->selectRaw('
                students.id,
                students.fullname,
                students.municipality,
                0 as panelist_count,
                0 as team_count,
                NULL as teams,
                NULL as panelists,
                NULL as rating_dates
            ')
            ->orderBy('students.fullname')
            ->limit(self::DRILL_DOWN_LIMIT)
            ->get();
    }

    protected function applyRatingStatusConstraint(Builder $query, string $status): Builder
    {
        return match ($status) {
            'complete' => $query
                ->where('rating_summary.panelist_count', 3)
                ->where('rating_summary.team_count', 1),
            'incomplete' => $query
                ->where('rating_summary.panelist_count', '>', 0)
                ->where('rating_summary.panelist_count', '<', 3),
            'invalid_mixed_team' => $query->where('rating_summary.team_count', '>', 1),
            default => $query,
        };
    }

    protected function studentRatingSummaryQuery(): Builder
    {
        return $this->filteredScoreRowsQuery()
            ->selectRaw('
                student_scores.student_id,
                COUNT(DISTINCT student_scores.user_id) as panelist_count,
                COUNT(DISTINCT users.team) as team_count
            ')
            ->groupBy('student_scores.student_id');
    }

    protected function studentRatingDetailsQuery(): Builder
    {
        return $this->filteredScoreRowsQuery()
            ->selectRaw("
                student_scores.student_id,
                GROUP_CONCAT(DISTINCT users.team ORDER BY users.team SEPARATOR '||') as teams,
                GROUP_CONCAT(DISTINCT CONCAT(users.name, ' (', COALESCE(users.team, 'No Team'), ')') ORDER BY users.team, users.name SEPARATOR '||') as panelists,
                GROUP_CONCAT(DISTINCT DATE(student_scores.created_at) ORDER BY DATE(student_scores.created_at) SEPARATOR '||') as rating_dates
            ")
            ->groupBy('student_scores.student_id');
    }

    protected function filteredScoreRowsQuery(): Builder
    {
        return DB::table('student_scores')
            ->join('users', 'student_scores.user_id', '=', 'users.id')
            ->join('students', 'student_scores.student_id', '=', 'students.id')
            ->whereNull('student_scores.deleted_at')
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
            ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date))
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality));
    }

    protected function notRatedStudentsQuery(): Builder
    {
        return DB::table('students')
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->whereNotExists(function (Builder $query): void {
                $query
                    ->selectRaw('1')
                    ->from('student_scores')
                    ->join('users', 'student_scores.user_id', '=', 'users.id')
                    ->whereColumn('student_scores.student_id', 'students.id')
                    ->whereNull('student_scores.deleted_at')
                    ->whereNotNull('users.team')
                    ->where('users.team', '!=', '')
                    ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
                    ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date))
                    ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
                    ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId));
            });
    }

    /**
     * @return Collection<int, ?string>
     */
    protected function teamsForCoverage(): Collection
    {
        return DB::table('users')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->distinct()
            ->orderBy('users.team')
            ->pluck('users.team');
    }

    protected function teamRatedStudentCount(string $team): int
    {
        return DB::table('student_scores')
            ->join('users', 'student_scores.user_id', '=', 'users.id')
            ->join('students', 'student_scores.student_id', '=', 'students.id')
            ->whereNull('student_scores.deleted_at')
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->where('users.team', $team)
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
            ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date))
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->distinct()
            ->count('student_scores.student_id');
    }

    protected function ratedRowsQuery(): Builder
    {
        return DB::table('student_scores')
            ->join('users', 'student_scores.user_id', '=', 'users.id')
            ->join('students', 'student_scores.student_id', '=', 'students.id')
            ->whereNull('student_scores.deleted_at')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
            ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date))
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->selectRaw('
                users.id as panelist_id,
                users.name as panelist_name,
                users.team as team,
                DATE(student_scores.created_at) as interview_date,
                COUNT(DISTINCT students.id) as rated_students_count
            ')
            ->groupBy('users.id', 'users.name', 'users.team', DB::raw('DATE(student_scores.created_at)'))
            ->orderBy('users.team')
            ->orderBy('users.name')
            ->orderByDesc('interview_date');
    }

    protected function unratedRowsQuery(): Builder
    {
        return DB::query()
            ->fromSub($this->panelistStudentBaseQuery(), 'panelist_students')
            ->leftJoin('student_scores', function ($join): void {
                $join
                    ->on('student_scores.user_id', '=', 'panelist_students.panelist_id')
                    ->on('student_scores.student_id', '=', 'panelist_students.student_id')
                    ->whereNull('student_scores.deleted_at');

                if ($this->startDate) {
                    $join->whereDate('student_scores.created_at', '>=', $this->startDate);
                }

                if ($this->endDate) {
                    $join->whereDate('student_scores.created_at', '<=', $this->endDate);
                }
            })
            ->whereNull('student_scores.id')
            ->selectRaw('
                panelist_students.panelist_id,
                panelist_students.panelist_name,
                panelist_students.team,
                COUNT(panelist_students.student_id) as unrated_students_count
            ')
            ->groupBy('panelist_students.panelist_id', 'panelist_students.panelist_name', 'panelist_students.team')
            ->orderBy('panelist_students.team')
            ->orderBy('panelist_students.panelist_name');
    }

    protected function teamRatedCountsQuery(): Builder
    {
        return DB::query()
            ->fromSub($this->teamsBaseQuery(), 'teams')
            ->leftJoin('users as team_panelists', function ($join): void {
                $join
                    ->on('team_panelists.team', '=', 'teams.team')
                    ->whereIn('team_panelists.id', $this->panelistIdsQuery());

                if ($this->panelistId) {
                    $join->where('team_panelists.id', $this->panelistId);
                }
            })
            ->leftJoin('student_scores', function ($join): void {
                $join
                    ->on('student_scores.user_id', '=', 'team_panelists.id')
                    ->whereNull('student_scores.deleted_at');

                if ($this->startDate) {
                    $join->whereDate('student_scores.created_at', '>=', $this->startDate);
                }

                if ($this->endDate) {
                    $join->whereDate('student_scores.created_at', '<=', $this->endDate);
                }
            })
            ->leftJoin('students', function ($join): void {
                $join->on('students.id', '=', 'student_scores.student_id');

                if ($this->municipality) {
                    $join->where('students.municipality', $this->municipality);
                }
            })
            ->selectRaw('teams.team as team, COUNT(DISTINCT students.id) as rated_students')
            ->groupBy('teams.team')
            ->orderBy('teams.team');
    }

    /**
     * @return array<int, string>
     */
    protected function ratedStudentSamples(int $panelistId, string $interviewDate): array
    {
        return DB::table('student_scores')
            ->join('students', 'student_scores.student_id', '=', 'students.id')
            ->whereNull('student_scores.deleted_at')
            ->where('student_scores.user_id', $panelistId)
            ->whereDate('student_scores.created_at', $interviewDate)
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->distinct()
            ->orderBy('students.fullname')
            ->limit(self::STUDENT_SAMPLE_LIMIT + 1)
            ->pluck('students.fullname')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function unratedStudentSamples(int $panelistId): array
    {
        return DB::table('students')
            ->whereNotExists(function (Builder $query) use ($panelistId): void {
                $query
                    ->selectRaw('1')
                    ->from('student_scores')
                    ->whereColumn('student_scores.student_id', 'students.id')
                    ->where('student_scores.user_id', $panelistId)
                    ->whereNull('student_scores.deleted_at')
                    ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
                    ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date));
            })
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->orderBy('students.fullname')
            ->limit(self::STUDENT_SAMPLE_LIMIT + 1)
            ->pluck('students.fullname')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function teamUnratedStudentSamples(string $team): array
    {
        return DB::table('students')
            ->whereNotExists(function (Builder $query) use ($team): void {
                $query
                    ->selectRaw('1')
                    ->from('student_scores')
                    ->join('users', 'student_scores.user_id', '=', 'users.id')
                    ->whereColumn('student_scores.student_id', 'students.id')
                    ->whereNull('student_scores.deleted_at')
                    ->whereNotNull('users.team')
                    ->where('users.team', '!=', '')
                    ->where('users.team', $team)
                    ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
                    ->when($this->startDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
                    ->when($this->endDate, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date));
            })
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->orderBy('students.fullname')
            ->limit(self::STUDENT_SAMPLE_LIMIT + 1)
            ->pluck('students.fullname')
            ->all();
    }

    protected function panelistStudentBaseQuery(): Builder
    {
        return DB::query()
            ->fromSub($this->panelistsBaseQuery(), 'panelists')
            ->crossJoinSub($this->filteredStudentsQuery(), 'students')
            ->select([
                'panelists.panelist_id',
                'panelists.panelist_name',
                'panelists.team',
                'students.student_id',
                'students.student_name',
            ]);
    }

    protected function panelistsBaseQuery(): Builder
    {
        return DB::table('users')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->selectRaw('users.id as panelist_id, users.name as panelist_name, users.team as team');
    }

    protected function teamsBaseQuery(): Builder
    {
        return DB::table('users')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->when($this->team, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->panelistId, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->select('users.team')
            ->distinct();
    }

    protected function filteredStudentsQuery(): Builder
    {
        return DB::table('students')
            ->when($this->municipality, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->selectRaw('students.id as student_id, students.fullname as student_name');
    }

    protected function panelistIdsQuery(): Builder
    {
        return DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join
                    ->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'panelist')
            ->whereNotNull('users.team')
            ->where('users.team', '!=', '')
            ->select('users.id');
    }
}
