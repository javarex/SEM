<x-filament-panels::page>
    @php
        $studentSamples = fn (mixed $students): array => is_array($students) ? $students : (filled($students) ? explode('||', (string) $students) : []);
        $formatTeam = fn (?string $team): string => $teamOptions[$team] ?? 'No Team';
        $formatTeams = fn (mixed $teams): string => collect($studentSamples($teams))
            ->map(fn (?string $team): string => $formatTeam($team))
            ->implode(', ');
        $formatRatingDateTimes = fn (mixed $dates): string => collect($studentSamples($dates))
            ->filter()
            ->map(fn (string $date): string => \Carbon\Carbon::parse($date)->format('M j, Y g:i A'))
            ->implode(', ');
    @endphp

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Start Date
                    <input class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" type="date" wire:model.live="startDate">
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    End Date
                    <input class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" type="date" wire:model.live="endDate">
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Team
                    <select class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" wire:model.live="team">
                        <option value="">All Teams</option>
                        @foreach ($teamOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Panelist
                    <select class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" wire:model.live="panelistId">
                        <option value="">All Panelists</option>
                        @foreach ($panelists as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Municipality
                    <select class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" wire:model.live="municipality">
                        <option value="">All Municipalities</option>
                        @foreach ($municipalities as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                    Status
                    <select class="rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800" wire:model.live="status">
                        <option value="all">Rated and Unrated</option>
                        <option value="rated">Only Rated</option>
                        <option value="unrated">Only Unrated</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex justify-end">
                <button
                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-60"
                    type="button"
                    wire:click="export"
                    wire:loading.attr="disabled"
                    wire:target="export"
                >
                    Export Excel
                </button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($ratingStatusCards as $key => $card)
                <button
                    class="rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-primary-400 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500 dark:hover:bg-primary-500/10"
                    type="button"
                    wire:click="showRatingStatus('{{ $key }}')"
                >
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $card['label'] }}</div>
                    <div class="mt-2 text-3xl font-bold text-gray-950 dark:text-white">{{ number_format($card['count']) }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['description'] }}</div>
                </button>
            @endforeach
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($teamCoverageRows as $row)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ $row['team_label'] }}</div>
                    <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                        <div>
                            <div class="text-2xl font-bold text-success-600">{{ number_format($row['rated_students']) }}</div>
                            <div class="text-xs text-gray-500">Rated</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-danger-600">{{ number_format($row['unrated_students']) }}</div>
                            <div class="text-xs text-gray-500">Unrated</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary-600">{{ number_format($row['completion_percentage'], 2) }}%</div>
                            <div class="text-xs text-gray-500">Coverage</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Students without team rating</div>
                        <div class="mt-2 flex max-h-28 flex-wrap gap-1 overflow-y-auto">
                            @foreach (array_slice($studentSamples($row['unrated_student_names']), 0, 8) as $student)
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $student }}</span>
                            @endforeach
                            @if ($row['unrated_students'] > 8)
                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">+{{ number_format($row['unrated_students'] - 8) }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($showRated)
            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">A. Panelist Interviewed Students</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Team</th>
                                <th class="px-4 py-3">Panelist</th>
                                <th class="px-4 py-3">Interview Date</th>
                                <th class="px-4 py-3 text-center">Rated Count</th>
                                <th class="px-4 py-3">Students Rated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($ratedRows as $row)
                                <tr>
                                    <td class="px-4 py-3">{{ $formatTeam($row->team) }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $row->panelist_name }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($row->interview_date)->format('M j, Y') }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ number_format($row->rated_students_count) }}</td>
                                    <td class="max-w-xl px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (array_slice($studentSamples($row->rated_students), 0, 8) as $student)
                                                <span class="rounded-md bg-success-50 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ $student }}</span>
                                            @endforeach
                                            @if ($row->rated_students_count > 8)
                                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">+{{ number_format($row->rated_students_count - 8) }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-6 text-center text-gray-500" colspan="5">No rated students found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    {{ $ratedRows->links(data: ['scrollTo' => false]) }}
                </div>
            </section>
        @endif

        @if ($showUnrated)
            <section class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">B. Students Not Rated by Panelist</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Team</th>
                                <th class="px-4 py-3">Panelist</th>
                                <th class="px-4 py-3 text-center">Unrated Count</th>
                                <th class="px-4 py-3">Students Not Yet Rated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($unratedRows as $row)
                                <tr>
                                    <td class="px-4 py-3">{{ $formatTeam($row->team) }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $row->panelist_name }}</td>
                                    <td class="px-4 py-3 text-center font-semibold">{{ number_format($row->unrated_students_count) }}</td>
                                    <td class="max-w-xl px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (array_slice($studentSamples($row->unrated_students), 0, 8) as $student)
                                                <span class="rounded-md bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 dark:bg-danger-500/10 dark:text-danger-300">{{ $student }}</span>
                                            @endforeach
                                            @if ($row->unrated_students_count > 8)
                                                <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">+{{ number_format($row->unrated_students_count - 8) }} more</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-6 text-center text-gray-500" colspan="4">No unrated students found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                    {{ $unratedRows->links(data: ['scrollTo' => false]) }}
                </div>
            </section>
        @endif

        @if ($selectedRatingStatus)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4">
                <div class="flex max-h-[85vh] w-full max-w-6xl flex-col rounded-lg bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <div>
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $selectedRatingStatusLabel }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Showing up to {{ number_format(250) }} students matching the selected filters.</p>
                        </div>
                        <button
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                            type="button"
                            wire:click="closeRatingStatus"
                        >
                            Close
                        </button>
                    </div>

                    <div class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">Municipality</th>
                                    <th class="px-4 py-3 text-center">Rating Count</th>
                                    <th class="px-4 py-3">Teams Involved</th>
                                    <th class="px-4 py-3">Panelists</th>
                                    <th class="px-4 py-3">Rating Date/Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($selectedRatingStatusRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $row->fullname }}</td>
                                        <td class="px-4 py-3">{{ $row->municipality ?: 'N/A' }}</td>
                                        <td class="px-4 py-3 text-center font-semibold">{{ number_format($row->panelist_count) }}</td>
                                        <td class="px-4 py-3">{{ $formatTeams($row->teams) ?: 'N/A' }}</td>
                                        <td class="max-w-md px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($studentSamples($row->panelists) as $panelist)
                                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $panelist }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">{{ $formatRatingDateTimes($row->rating_dates) ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-6 text-center text-gray-500" colspan="6">No students found for this status.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
