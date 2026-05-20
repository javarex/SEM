<x-filament-panels::page>
    @php
        $teamOptions = \App\UserTeam::options();
        $criteria = [
            'emotional' => 'Emotional',
            'intelligence' => 'Intelligence',
            'socio_economic' => 'Socio-Economic',
        ];

        $formatScore = fn (mixed $value): string => $value === null ? 'N/A' : number_format((float) $value, 2);
    @endphp

    <div class="space-y-6 rounded-lg border border-[#d6b35f] bg-[#fffaf0] p-6 shadow-sm dark:border-[#8a6a2f] dark:bg-[#24180f]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase text-[#8a5a12] dark:text-[#f3c95f]">Consolidated Score</p>
                <h2 class="text-2xl font-bold text-[#3f2615] dark:text-[#fff3cf]">Judging Table</h2>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="flex flex-col gap-1 text-sm font-semibold text-[#5d3a1a] dark:text-[#f5d889]">
                    Panelist Team
                    <select
                        class="rounded-lg border border-[#d6b35f] bg-white px-3 py-2 text-sm text-[#3f2615] shadow-sm dark:border-[#8a6a2f] dark:bg-[#2f2015] dark:text-[#fff3cf]"
                        wire:model.live="team"
                    >
                        <option value="">All Teams</option>
                        @foreach ($teamOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="rounded-lg border border-[#d6b35f] bg-white px-4 py-3 text-sm text-[#5d3a1a] shadow-sm dark:border-[#8a6a2f] dark:bg-[#2f2015] dark:text-[#f5d889]">
                    <span class="font-semibold">{{ number_format($students->total()) }}</span>
                    ranked students
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-[#d6b35f] bg-white shadow-sm dark:border-[#8a6a2f] dark:bg-[#2f2015]">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-[#6f451c] text-[#fff7dc]">
                        <th class="sticky left-0 z-20 min-w-96 border-r border-[#d6b35f] bg-[#6f451c] px-4 py-3 text-left font-semibold">
                            Student Name
                        </th>
                        @foreach ($judges as $judge)
                            <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold" colspan="3">
                                <span>{{ $judge['name'] }}</span>
                                <span class="block text-xs font-medium opacity-80">{{ $teamOptions[$judge['team']] ?? 'No Team' }}</span>
                            </th>
                        @endforeach
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Exam Score</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Panel Total Avg</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Exam Score</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Panel Avg</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Final Average</th>
                        <th class="px-4 py-3 text-center font-semibold">Rank</th>
                    </tr>
                    <tr class="bg-[#f2d27a] text-[#3f2615] dark:bg-[#8a6a2f] dark:text-[#fff3cf]">
                        <th class="sticky left-0 z-20 min-w-96 border-r border-[#d6b35f] bg-[#f2d27a] px-4 py-3 text-left font-semibold dark:bg-[#8a6a2f]">
                            Criteria
                        </th>
                        @foreach ($judges as $judge)
                            @foreach ($criteria as $label)
                                <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">{{ $label }}</th>
                            @endforeach
                        @endforeach
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Raw</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Raw Average</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">50%</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">50%</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Combined</th>
                        <th class="px-4 py-3 text-center font-semibold">Place</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ead8a4] dark:divide-[#6f552a]">
                    @forelse ($students as $student)
                        <tr class="text-[#3f2615] even:bg-[#fff7df] hover:bg-[#f8e7b6] dark:text-[#fff3cf] dark:even:bg-[#352316] dark:hover:bg-[#44301d]">
                            <td class="sticky left-0 z-10 min-w-96 border-r border-[#ead8a4] bg-inherit px-4 py-3 text-sm font-semibold">
                                {{ $student['name'] }}
                            </td>

                            @foreach ($judges as $judge)
                                @foreach (array_keys($criteria) as $column)
                                    <td class="border-r border-[#ead8a4] px-4 py-3 text-center">
                                        {{ $formatScore($student['grades'][$judge['id']][$column] ?? null) }}
                                    </td>
                                @endforeach
                            @endforeach

                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $formatScore($student['examScore']) }}</td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $formatScore($student['averageScore']) }}</td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $formatScore($student['examScoreWeighted']) }}</td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $formatScore($student['panelScoreWeighted']) }}</td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $formatScore($student['finalAverage']) }}</td>
                            <td class="px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]">{{ $student['rank'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-[#5d3a1a] dark:text-[#f5d889]" colspan="{{ 7 + (count($judges) * 3) }}">
                                No scored students found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 text-sm text-[#5d3a1a] sm:flex-row sm:items-center sm:justify-between dark:text-[#f5d889]">
            <div class="text-center sm:text-left">
                Showing
                <span class="font-semibold">{{ number_format($students->firstItem() ?? 0) }}</span>
                to
                <span class="font-semibold">{{ number_format($students->lastItem() ?? 0) }}</span>
                of
                <span class="font-semibold">{{ number_format($students->total()) }}</span>
                ranked students
            </div>

            <div class="flex items-center justify-center gap-2">
                <button
                    class="rounded-lg border border-[#b78b2e] bg-white px-3 py-2 font-semibold text-[#5d3a1a] shadow-sm transition hover:bg-[#fff7df] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#8a6a2f] dark:bg-[#2f2015] dark:text-[#f5d889] dark:hover:bg-[#352316]"
                    type="button"
                    wire:click="previousPage"
                    wire:loading.attr="disabled"
                    @disabled($students->onFirstPage())
                >
                    Previous
                </button>

                <span class="min-w-24 rounded-lg border border-[#d6b35f] bg-[#fff7df] px-3 py-2 text-center font-semibold text-[#3f2615] dark:border-[#8a6a2f] dark:bg-[#352316] dark:text-[#fff3cf]">
                    Page {{ number_format($students->currentPage()) }} of {{ number_format($students->lastPage()) }}
                </span>

                <button
                    class="rounded-lg border border-[#b78b2e] bg-[#7a4b14] px-3 py-2 font-semibold text-white shadow-sm transition hover:bg-[#6f451c] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#f3c95f]"
                    type="button"
                    wire:click="nextPage"
                    wire:loading.attr="disabled"
                    @disabled(! $students->hasMorePages())
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
