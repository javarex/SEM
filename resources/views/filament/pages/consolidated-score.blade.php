<x-filament-panels::page>
    @php
        $teamOptions = \App\UserTeam::options();
    @endphp

    <div
        class="space-y-6 rounded-lg border border-[#d6b35f] bg-[#fffaf0] p-6 shadow-sm dark:border-[#8a6a2f] dark:bg-[#24180f]"
        x-data="{
            students: @entangle('scores'),
            judges: @entangle('judges'),
            teamOptions: @js($teamOptions),
            perPage: 20,
            currentPage: 1,
            paginatedStudents() {
                return this.students.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage);
            },
            totalPages() {
                return Math.max(Math.ceil(this.students.length / this.perPage), 1);
            },
            scoreFor(student, judge, column) {
                return student.grades[judge.id]?.[column] ?? 'N/A';
            },
            teamLabel(team) {
                return this.teamOptions[team] ?? 'No Team';
            },
            nextPage() {
                if (this.currentPage < this.totalPages()) {
                    this.currentPage++;
                }
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            }
        }"
    >
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
                        x-on:change="currentPage = 1"
                    >
                        <option value="">All Teams</option>
                        @foreach ($teamOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="rounded-lg border border-[#d6b35f] bg-white px-4 py-3 text-sm text-[#5d3a1a] shadow-sm dark:border-[#8a6a2f] dark:bg-[#2f2015] dark:text-[#f5d889]">
                    <span class="font-semibold" x-text="students.length"></span>
                    ranked students
                </div>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-[#d6b35f] bg-white shadow-sm dark:border-[#8a6a2f] dark:bg-[#2f2015]">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-[#6f451c] text-[#fff7dc]">
                        <th class="sticky left-0 z-20 border-r border-[#d6b35f] bg-[#6f451c] px-4 py-3 text-left font-semibold">
                            Student Name
                        </th>
                        <template x-for="judge in judges" :key="judge.id">
                            <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold" colspan="3">
                                <span x-text="judge.name"></span>
                                <span class="block text-xs font-medium opacity-80" x-text="teamLabel(judge.team)"></span>
                            </th>
                        </template>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Exam Score</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Panel Total Avg</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Exam Score</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Panel Avg</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Final Average</th>
                        <th class="px-4 py-3 text-center font-semibold">Rank</th>
                    </tr>
                    <tr class="bg-[#f2d27a] text-[#3f2615] dark:bg-[#8a6a2f] dark:text-[#fff3cf]">
                        <th class="sticky left-0 z-20 border-r border-[#d6b35f] bg-[#f2d27a] px-4 py-3 text-left font-semibold dark:bg-[#8a6a2f]">
                            Criteria
                        </th>
                        <template x-for="judge in judges" :key="`criteria-${judge.id}`">
                            <template x-for="column in ['Emotional', 'Intelligence', 'Socio-Economic']" :key="`${judge.id}-${column}`">
                                <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold" x-text="column"></th>
                            </template>
                        </template>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Raw</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Raw Average</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">50%</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">50%</th>
                        <th class="border-r border-[#d6b35f] px-4 py-3 text-center font-semibold">Combined</th>
                        <th class="px-4 py-3 text-center font-semibold">Place</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ead8a4] dark:divide-[#6f552a]">
                    <template x-for="student in paginatedStudents()" :key="student.name">
                        <tr class="text-[#3f2615] even:bg-[#fff7df] hover:bg-[#f8e7b6] dark:text-[#fff3cf] dark:even:bg-[#352316] dark:hover:bg-[#44301d]">
                            <td class="sticky left-0 z-10 border-r border-[#ead8a4] bg-inherit px-4 py-3 font-semibold">
                                <span x-text="student.name"></span>
                            </td>

                            <template x-for="judge in judges" :key="`score-${student.name}-${judge.id}`">
                                <template x-for="column in ['emotional', 'intelligence', 'socio_economic']" :key="`${student.name}-${judge.id}-${column}`">
                                    <td class="border-r border-[#ead8a4] px-4 py-3 text-center" x-text="scoreFor(student, judge, column)"></td>
                                </template>
                            </template>

                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="Number(student.examScore).toFixed(2)"></td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="Number(student.averageScore).toFixed(2)"></td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="Number(student.examScoreWeighted).toFixed(2)"></td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="Number(student.panelScoreWeighted).toFixed(2)"></td>
                            <td class="border-r border-[#ead8a4] px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="Number(student.finalAverage).toFixed(2)"></td>
                            <td class="px-4 py-3 text-center font-bold text-[#7a4b14] dark:text-[#f3c95f]" x-text="student.rank"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 text-sm text-[#5d3a1a] sm:flex-row sm:items-center sm:justify-between dark:text-[#f5d889]">
            <button
                class="rounded-lg border border-[#b78b2e] bg-[#7a4b14] px-4 py-2 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#f3c95f]"
                type="button"
                @click="prevPage()"
                :disabled="currentPage === 1"
            >
                Previous
            </button>

            <span class="text-center font-semibold">
                Page <span x-text="currentPage"></span> of <span x-text="totalPages()"></span>
            </span>

            <button
                class="rounded-lg border border-[#b78b2e] bg-[#7a4b14] px-4 py-2 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#f3c95f]"
                type="button"
                @click="nextPage()"
                :disabled="currentPage === totalPages()"
            >
                Next
            </button>
        </div>
    </div>
</x-filament-panels::page>
