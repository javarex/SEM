<x-filament-panels::page>
    <div class=" bg-white p-6 shadow-lg rounded-lg" x-data="judgingTable()">
        <h2 class="text-xl font-bold mb-4 text-gray-700">Judging Table</h2>

        <table class="w-full border-collapse border border-gray-300">
            <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-4 py-2" rowspan="2">Judge</th>
                <th class="border border-gray-300 px-4 py-2" colspan="3">Judge 1</th>
                <th class="border border-gray-300 px-4 py-2" colspan="3">Judge 2</th>
            </tr>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-4 py-2">Emotional</th>
                <th class="border border-gray-300 px-4 py-2">Intelligence</th>
                <th class="border border-gray-300 px-4 py-2">Socio-Economic Form</th>
                <th class="border border-gray-300 px-4 py-2">Emotional</th>
                <th class="border border-gray-300 px-4 py-2">Intelligence</th>
                <th class="border border-gray-300 px-4 py-2">Socio-Economic Form</th>
            </tr>
            </thead>
            <tbody>
            <template x-for="(row, index) in rows" :key="index">
                <tr class="bg-white">
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="text" class="w-full px-2 py-1 border rounded" x-model="row.judge">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge1_emotional">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge1_intelligence">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge1_socio">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge2_emotional">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge2_intelligence">
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        <input type="number" min="0" max="10" class="w-full px-2 py-1 border rounded" x-model="row.judge2_socio">
                    </td>
                    <td class="border border-gray-300 px-4 py-2 text-center">
                        <button @click="removeRow(index)" class="bg-red-500 text-white px-2 py-1 rounded">✖</button>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>

        <div class="mt-4 flex justify-between">
            <button @click="addRow()" class="bg-blue-500 text-white px-4 py-2 rounded">+ Add Row</button>
        </div>
    </div>

    <script>
        function judgingTable() {
            return {
                rows: [
                    { judge: 'Judge 1', judge1_emotional: '', judge1_intelligence: '', judge1_socio: '', judge2_emotional: '', judge2_intelligence: '', judge2_socio: '' },
                ],
                addRow() {
                    this.rows.push({ judge: '', judge1_emotional: '', judge1_intelligence: '', judge1_socio: '', judge2_emotional: '', judge2_intelligence: '', judge2_socio: '' });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                }
            }
        }
    </script>
</x-filament-panels::page>
