@php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    $attributes = $record->getAttributes();
    $columns = collect(Schema::getColumnListing($record->getTable()));

    $formatLabel = fn (string $key): string => Str::of($key)
        ->replace('_', ' ')
        ->title()
        ->toString();

    $formatValue = function (mixed $value): string {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_float($value)) {
            return rtrim(rtrim(number_format($value, 2), '0'), '.');
        }

        return (string) $value;
    };

    $hiddenColumns = [
        'id',
        'first_name',
        'middle_name',
        'last_name',
        'fullname',
        'created_at',
        'updated_at',
    ];

    $hiddenKeywords = ['score', 'remarks'];

    $fields = $columns
        ->reject(fn (string $column): bool => in_array($column, $hiddenColumns, true) || Str::contains($column, $hiddenKeywords))
        ->map(fn (string $column): array => [
            'key' => $column,
            'label' => $formatLabel($column),
            'displayValue' => $formatValue($attributes[$column] ?? null),
            'isBadge' => is_bool($attributes[$column] ?? null) || Str::contains($column, 'status'),
            'isEmpty' => ($attributes[$column] ?? null) === null || ($attributes[$column] ?? null) === '',
        ]);

    $displayName = $attributes['fullname'] ?? trim(($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? ''));
    $initials = Str::of($displayName)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
        ->implode('') ?: 'ST';
@endphp

<div
    x-data="{ isDetailsOpen: true }"
    class="space-y-5"
>
    <header class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 via-emerald-400 to-sky-400"></div>

        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-300 to-amber-500 text-2xl font-black tracking-tight text-gray-950 shadow-lg shadow-amber-500/20">
                {{ $initials }}
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-700 dark:text-amber-300">
                    Student Profile
                </p>

                <h2 class="mt-2 truncate text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                    {{ $displayName ?: 'Unnamed Student' }}
                </h2>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    A focused overview of non-redundant student profile details.
                </p>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <button
            type="button"
            x-on:click="isDetailsOpen = ! isDetailsOpen"
            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:hover:bg-gray-800/70"
        >
            <span>
                <span class="block text-base font-bold text-gray-950 dark:text-white">
                    Profile Details
                </span>

                <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                    {{ $fields->count() }} {{ Str::plural('field', $fields->count()) }}
                </span>
            </span>

            <svg
                x-bind:class="{ 'rotate-180': isDetailsOpen }"
                class="h-5 w-5 text-gray-400 transition-transform"
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
            >
                <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="isDetailsOpen" x-collapse>
            @if ($fields->isEmpty())
                <div class="border-t border-gray-100 p-5 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    No additional profile details to display.
                </div>
            @else
                <dl class="grid gap-3 border-t border-gray-100 p-5 dark:border-gray-800 sm:grid-cols-2">
                    @foreach ($fields as $field)
                        <div class="rounded-xl bg-gray-50 p-4 ring-1 ring-gray-100 transition hover:bg-white hover:shadow-sm dark:bg-gray-800/60 dark:ring-gray-700 dark:hover:bg-gray-800">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                {{ $field['label'] }}
                            </dt>

                            <dd class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">
                                @if ($field['isBadge'])
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1',
                                        'bg-gray-100 text-gray-600 ring-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600' => $field['isEmpty'],
                                        'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-500/30' => ! $field['isEmpty'],
                                    ])>
                                        {{ $field['displayValue'] }}
                                    </span>
                                @else
                                    <span @class([
                                        'break-words',
                                        'text-gray-400 dark:text-gray-500' => $field['isEmpty'],
                                    ])>
                                        {{ $field['displayValue'] }}
                                    </span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>
    </section>
</div>
