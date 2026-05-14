@php
    $teamLabels = [
        'team_1' => 'Team 1',
        'team_2' => 'Team 2',
        'team_3' => 'Team 3',
    ];

    $formatScore = fn (mixed $value): string => $value === null ? 'N/A' : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
@endphp

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3">Panelist</th>
                <th class="px-4 py-3">Team</th>
                <th class="px-4 py-3 text-center">Emotional</th>
                <th class="px-4 py-3 text-center">Intelligence</th>
                <th class="px-4 py-3 text-center">Socio-Economic</th>
                <th class="px-4 py-3 text-center">Total</th>
                <th class="px-4 py-3">Remarks</th>
                <th class="px-4 py-3">Scored At</th>
                @if ($canDeleteScore)
                    <th class="px-4 py-3 text-right">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900">
            @forelse ($scores as $score)
                <tr class="text-gray-700 dark:text-gray-200">
                    <td class="px-4 py-3 font-medium">{{ $score->user?->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $teamLabels[$score->user?->team] ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-center">{{ $formatScore($score->emotional) }}</td>
                    <td class="px-4 py-3 text-center">{{ $formatScore($score->intelligence) }}</td>
                    <td class="px-4 py-3 text-center">{{ $formatScore($score->socio_economic) }}</td>
                    <td class="px-4 py-3 text-center font-semibold">{{ $formatScore($score->totalScore) }}</td>
                    <td class="max-w-xs whitespace-pre-line px-4 py-3">{{ filled($score->remarks) ? $score->remarks : 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $score->created_at?->format('M j, Y g:i A') ?? 'N/A' }}</td>
                    @if ($canDeleteScore)
                        <td class="px-4 py-3 text-right">
                            <x-filament::icon-button
                                color="danger"
                                icon="heroicon-o-trash"
                                label="Delete panelist score"
                                x-on:click="if (confirm('Delete this panelist score?')) { $wire.deletePanelistScore({{ $score->id }}) }"
                            />
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-6 text-center text-gray-500 dark:text-gray-400" colspan="{{ $canDeleteScore ? 9 : 8 }}">
                        No panel scores found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
