<?php

namespace App\Exports;

use App\Exports\Sheets\PanelInterviewMonitoringSheet;
use App\Models\User;
use App\UserTeam;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PanelInterviewMonitoringReportExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {}

    /**
     * @return array<int, PanelInterviewMonitoringSheet>
     */
    public function sheets(): array
    {
        $sheets = [];

        if (($this->filters['status'] ?? 'all') !== 'unrated') {
            $sheets[] = new PanelInterviewMonitoringSheet(
                $this->ratedRows(),
                ['Team', 'Panelist', 'Interview Date', 'Rated Students Count', 'Students Rated'],
                'Rated'
            );
        }

        if (($this->filters['status'] ?? 'all') !== 'rated') {
            $sheets[] = new PanelInterviewMonitoringSheet(
                $this->unratedRows(),
                ['Team', 'Panelist', 'Unrated Students Count', 'Students Not Yet Rated'],
                'Unrated'
            );
        }

        $sheets[] = new PanelInterviewMonitoringSheet(
            $this->teamCoverageRows(),
            ['Team', 'Total Students Rated by Team', 'Total Students Not Yet Rated by Team', 'Completion Percentage', 'Students Without Team Rating'],
            'Team Coverage'
        );

        return $sheets;
    }

    protected function ratedRows(): Collection
    {
        return $this->ratedRowsQuery()
            ->get()
            ->map(fn ($row): array => [
                'team' => $this->teamLabel($row->team),
                'panelist' => $this->safeSpreadsheetText($row->panelist_name),
                'interview_date' => $row->interview_date,
                'rated_students_count' => (int) $row->rated_students_count,
                'rated_students' => $this->safeSpreadsheetText(str_replace('||', "\n", (string) $row->rated_students)),
            ]);
    }

    protected function unratedRows(): Collection
    {
        return $this->unratedRowsQuery()
            ->get()
            ->map(fn ($row): array => [
                'team' => $this->teamLabel($row->team),
                'panelist' => $this->safeSpreadsheetText($row->panelist_name),
                'unrated_students_count' => (int) $row->unrated_students_count,
                'unrated_students' => $this->safeSpreadsheetText(str_replace('||', "\n", (string) $row->unrated_students)),
            ]);
    }

    protected function teamCoverageRows(): Collection
    {
        $totalStudents = $this->filteredStudentCount();
        $unratedStudentsByTeam = $this->teamUnratedStudentsQuery()
            ->get()
            ->keyBy('team');

        return $this->teamRatedCountsQuery()
            ->get()
            ->map(function ($row) use ($totalStudents, $unratedStudentsByTeam): array {
                $ratedStudents = (int) $row->rated_students;
                $unratedStudents = max($totalStudents - $ratedStudents, 0);
                $unratedRow = $unratedStudentsByTeam->get($row->team);

                return [
                    'team' => $this->teamLabel($row->team),
                    'rated_students' => $ratedStudents,
                    'unrated_students' => $unratedStudents,
                    'completion_percentage' => $totalStudents > 0
                        ? round(($ratedStudents / $totalStudents) * 100, 2)
                        : 0.0,
                    'unrated_student_names' => $this->safeSpreadsheetText(str_replace('||', "\n", (string) $unratedRow?->unrated_students)),
                ];
            });
    }

    protected function ratedRowsQuery(): Builder
    {
        return DB::table('student_scores')
            ->join('users', 'student_scores.user_id', '=', 'users.id')
            ->join('students', 'student_scores.student_id', '=', 'students.id')
            ->whereNull('student_scores.deleted_at')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->when($this->filters['startDate'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '>=', $date))
            ->when($this->filters['endDate'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('student_scores.created_at', '<=', $date))
            ->when($this->filters['team'] ?? null, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->filters['panelistId'] ?? null, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->when($this->filters['municipality'] ?? null, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->selectRaw("
                users.id as panelist_id,
                users.name as panelist_name,
                users.team as team,
                DATE(student_scores.created_at) as interview_date,
                COUNT(DISTINCT students.id) as rated_students_count,
                GROUP_CONCAT(DISTINCT students.fullname ORDER BY students.fullname SEPARATOR '||') as rated_students
            ")
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

                if ($this->filters['startDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '>=', $this->filters['startDate']);
                }

                if ($this->filters['endDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '<=', $this->filters['endDate']);
                }
            })
            ->whereNull('student_scores.id')
            ->selectRaw("
                panelist_students.panelist_id,
                panelist_students.panelist_name,
                panelist_students.team,
                COUNT(panelist_students.student_id) as unrated_students_count,
                GROUP_CONCAT(panelist_students.student_name ORDER BY panelist_students.student_name SEPARATOR '||') as unrated_students
            ")
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

                if ($this->filters['panelistId'] ?? null) {
                    $join->where('team_panelists.id', $this->filters['panelistId']);
                }
            })
            ->leftJoin('student_scores', function ($join): void {
                $join
                    ->on('student_scores.user_id', '=', 'team_panelists.id')
                    ->whereNull('student_scores.deleted_at');

                if ($this->filters['startDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '>=', $this->filters['startDate']);
                }

                if ($this->filters['endDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '<=', $this->filters['endDate']);
                }
            })
            ->leftJoin('students', function ($join): void {
                $join->on('students.id', '=', 'student_scores.student_id');

                if ($this->filters['municipality'] ?? null) {
                    $join->where('students.municipality', $this->filters['municipality']);
                }
            })
            ->selectRaw('teams.team as team, COUNT(DISTINCT students.id) as rated_students')
            ->groupBy('teams.team')
            ->orderBy('teams.team');
    }

    protected function teamUnratedStudentsQuery(): Builder
    {
        return DB::query()
            ->fromSub($this->teamsBaseQuery(), 'teams')
            ->crossJoinSub($this->filteredStudentsQuery(), 'students')
            ->leftJoin('student_scores', function ($join): void {
                $join
                    ->on('student_scores.student_id', '=', 'students.student_id')
                    ->whereNull('student_scores.deleted_at');

                if ($this->filters['startDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '>=', $this->filters['startDate']);
                }

                if ($this->filters['endDate'] ?? null) {
                    $join->whereDate('student_scores.created_at', '<=', $this->filters['endDate']);
                }
            })
            ->leftJoin('users as scoring_panelists', function ($join): void {
                $join
                    ->on('scoring_panelists.id', '=', 'student_scores.user_id')
                    ->on('scoring_panelists.team', '=', 'teams.team');

                if ($this->filters['panelistId'] ?? null) {
                    $join->where('scoring_panelists.id', $this->filters['panelistId']);
                }
            })
            ->whereNull('scoring_panelists.id')
            ->selectRaw("
                teams.team as team,
                COUNT(DISTINCT students.student_id) as unrated_students_count,
                GROUP_CONCAT(DISTINCT students.student_name ORDER BY students.student_name SEPARATOR '||') as unrated_students
            ")
            ->groupBy('teams.team')
            ->orderBy('teams.team');
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
            ->when($this->filters['team'] ?? null, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->filters['panelistId'] ?? null, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->selectRaw('users.id as panelist_id, users.name as panelist_name, users.team as team');
    }

    protected function teamsBaseQuery(): Builder
    {
        return DB::table('users')
            ->whereIn('users.id', $this->panelistIdsQuery())
            ->when($this->filters['team'] ?? null, fn (Builder $query, string $team): Builder => $query->where('users.team', $team))
            ->when($this->filters['panelistId'] ?? null, fn (Builder $query, string $panelistId): Builder => $query->where('users.id', $panelistId))
            ->select('users.team')
            ->distinct();
    }

    protected function filteredStudentsQuery(): Builder
    {
        return DB::table('students')
            ->when($this->filters['municipality'] ?? null, fn (Builder $query, string $municipality): Builder => $query->where('students.municipality', $municipality))
            ->selectRaw('students.id as student_id, students.fullname as student_name');
    }

    protected function filteredStudentCount(): int
    {
        return $this->filteredStudentsQuery()->count();
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
            ->select('users.id');
    }

    protected function teamLabel(?string $team): string
    {
        return UserTeam::tryFrom((string) $team)?->getLabel() ?? 'No Team';
    }

    protected function safeSpreadsheetText(?string $value): ?string
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
