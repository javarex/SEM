<x-filament-panels::page>
    <div class="bg-white p-6 shadow-lg rounded-lg"
         x-data="{
                students: @entangle('scores'), // Ensure it's an array
                judges: @js($judges),
                perPage: 20,
                currentPage: 1,
                paginatedStudents() {
                    let start = (this.currentPage - 1) * this.perPage;
                    let end = start + this.perPage;
                    return this.students.slice(start, end);
                },
                totalPages() {
                    return Math.ceil(this.students.length / this.perPage);
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
    x-init="() => console.log(students)">
        <h2 class="text-xl font-bold mb-4 text-gray-700">Judging Table</h2>

        <table class="w-full border-collapse border border-gray-300">
            <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-4 py-2">Student Name</th>
                <template x-for="(judge, index) in judges" :key="index">
                    <th class="border border-gray-300 px-4 py-2 text-center" colspan="3" x-text="judge.name"></th>
                </template>
                <th class="border border-gray-300 px-4 py-2" colspan="2"></th>
            </tr>
            <tr class="bg-gray-200">
                    <th></th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Emotional</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Intelligence</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Socio-Economic</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Emotional</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Intelligence</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Socio-Economic</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Total Avg</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Rank</th>
            </tr>
            </thead>
            <tbody>
            <template x-for="student in paginatedStudents()" :key="student.name">
                <tr>
                    <td class="border border-gray-300 px-4 py-2" x-text="student.name"></td>

                    <!-- Instead of wrapping inside <template>, use x-for directly on <td> -->
                    <template x-for="(scores, judge) in student.grades"
                              :key="'judge-' + judge"
                              >
                        <template x-for="grade in scores">

                            <td class="border border-gray-300 px-4 py-2" x-text="grade"></td>
                        </template>
{{--                        <td class="border border-gray-300 px-4 py-2" x-text="scores.intelligence ?? 'N/A'"></td>--}}
{{--                        <td class="border border-gray-300 px-4 py-2" x-text="scores.socio_economic ?? 'N/A'"></td>--}}
                    </template>
                    <td class="border font-bold border-gray-300 px-4 py-2" x-text="student.averageScore"></td>
                    <td class="border font-bold border-gray-300 px-4 py-2" x-text="student.rank"></td>
                </tr>
            </template>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <div class="mt-4 flex justify-between">
            <button @click="prevPage()" :disabled="currentPage === 1" class="bg-gray-300 px-4 py-2 rounded disabled:opacity-50">Previous</button>
            <span class="text-gray-700">Page <span x-text="currentPage"></span> of <span x-text="totalPages()"></span></span>
            <button @click="nextPage()" :disabled="currentPage === totalPages()" class="bg-gray-300 px-4 py-2 rounded disabled:opacity-50">Next</button>
        </div>
    </div>

</x-filament-panels::page>
