@php
    use Illuminate\Support\Str;

    $attributes = $record->getAttributes();

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

        if ($value instanceof DateTimeInterface) {
            return $value->format('M j, Y g:i A');
        }

        return (string) $value;
    };

    $field = function (string $key, ?string $label = null, ?string $value = null, ?string $visibility = null) use ($attributes, $formatLabel, $formatValue): array {
        $rawValue = $value ?? ($attributes[$key] ?? null);

        return [
            'key' => $key,
            'label' => $label ?? $formatLabel($key),
            'value' => $formatValue($rawValue),
            'isEmpty' => $rawValue === null || $rawValue === '',
            'visibility' => $visibility ?? $key,
        ];
    };

    $displayName = $attributes['fullname'] ?? trim(($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? ''));
    $initials = Str::of($displayName)
        ->replace(',', ' ')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
        ->implode('') ?: 'ST';

    $location = collect([
        $attributes['purok'] ?? null ? 'Purok '.$attributes['purok'] : null,
        $attributes['barangay'] ?? null,
        $attributes['municipality'] ?? null,
    ])
        ->filter()
        ->implode(', ');

    $quickFacts = [
        $field('sex'),
        $field('type'),
        $field('category'),
        $field('ethnicity'),
        $field('family_background'),
        $field('exam_score', 'Score', null, 'score'),
        $field('ranking'),
    ];

    $sections = [
        'details' => [
            'label' => 'Details',
            'fields' => [
                $field('school'),
                $field('type'),
                $field('category'),
                $field('ethnicity'),
                $field('family_background'),
            ],
        ],
        'assessment' => [
            'label' => 'Assessment',
            'fields' => [
                $field('exam_score', 'Score', null, 'score'),
                $field('ranking'),
            ],
        ],
        'remarks' => [
            'label' => 'Remarks',
            'fields' => [
                $field('pcro_remarks', 'PCRO Remarks', null, 'remarks'),
                $field('cao_remarks', 'CAO Remarks', null, 'remarks'),
                $field('ydd_remarks', 'YDD Remarks', null, 'remarks'),
            ],
            'wide' => true,
        ],
        'system' => [
            'label' => 'System',
            'fields' => [
                $field('id', 'Record ID', null, 'system'),
                $field('created_at', 'Created', null, 'system'),
                $field('updated_at', 'Updated', null, 'system'),
            ],
        ],
    ];

    $visibilityControls = [
        'name' => 'Name',
        'address' => 'Address',
        'sex' => 'Sex',
        'type' => 'Type',
        'category' => 'Category',
        'ethnicity' => 'Ethnicity',
        'family_background' => 'Family Background',
        'remarks' => 'Remarks',
        'school' => 'School',
        'score' => 'Score',
        'ranking' => 'Ranking',
        'system' => 'System',
    ];

    $defaultVisibility = [
        'name' => true,
        'address' => true,
        'sex' => true,
        'type' => true,
        'category' => true,
        'ethnicity' => true,
        'family_background' => true,
        'remarks' => true,
        'school' => false,
        'score' => false,
        'ranking' => false,
        'system' => false,
    ];

    $fieldHasValue = fn (string $key): bool => filled($attributes[$key] ?? null);

    $filledVisibility = [
        'name' => filled($displayName),
        'address' => $location !== '',
        'sex' => $fieldHasValue('sex'),
        'type' => $fieldHasValue('type'),
        'category' => $fieldHasValue('category'),
        'ethnicity' => $fieldHasValue('ethnicity'),
        'family_background' => $fieldHasValue('family_background'),
        'remarks' => $fieldHasValue('pcro_remarks') || $fieldHasValue('cao_remarks') || $fieldHasValue('ydd_remarks'),
        'school' => $fieldHasValue('school'),
        'score' => $fieldHasValue('exam_score'),
        'ranking' => $fieldHasValue('ranking'),
        'system' => $fieldHasValue('id') || $fieldHasValue('created_at') || $fieldHasValue('updated_at'),
    ];

    $sectionFields = collect($sections)
        ->map(fn (array $section): array => collect($section['fields'])
            ->pluck('visibility')
            ->unique()
            ->values()
            ->all())
        ->all();

    $quickFactFields = collect($quickFacts)
        ->pluck('visibility')
        ->unique()
        ->values()
        ->all();

    $canCustomizeFields = auth()->user()?->hasRole('super_admin') ?? false;
@endphp

<div
    x-data="{
        activeSection: 'details',
        controlsOpen: false,
        visible: @js($defaultVisibility),
        filled: @js($filledVisibility),
        sectionFields: @js($sectionFields),
        quickFactFields: @js($quickFactFields),
        fieldVisible(key) {
            return Boolean(this.visible[key] && this.filled[key])
        },
        sectionVisible(section) {
            return (this.sectionFields[section] ?? []).some((key) => this.fieldVisible(key))
        },
        quickFactsVisible() {
            return this.quickFactFields.some((key) => this.fieldVisible(key))
        },
        firstVisibleSection() {
            return Object.keys(this.sectionFields).find((section) => this.sectionVisible(section)) ?? null
        },
        setActiveSection() {
            if (! this.sectionVisible(this.activeSection)) {
                this.activeSection = this.firstVisibleSection()
            }
        },
    }"
    x-init="setActiveSection()"
    x-effect="setActiveSection()"
    class="space-y-4"
>
    <header class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                <div
                    x-show="fieldVisible('name')"
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-xl font-bold text-white"
                >
                    {{ $initials }}
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                Student Profile
                            </p>

                            <h2
                                x-show="fieldVisible('name')"
                                class="mt-1 break-words text-2xl font-bold text-gray-950 dark:text-white"
                            >
                                {{ $displayName ?: 'Unnamed Student' }}
                            </h2>
                        </div>

                        @if (filled($attributes['category'] ?? null))
                            <span
                                x-show="fieldVisible('category')"
                                class="inline-flex w-fit rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/30"
                            >
                                {{ $attributes['category'] }}
                            </span>
                        @endif
                    </div>

                    @if ($location !== '')
                        <p
                            x-show="fieldVisible('address')"
                            class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300"
                        >
                            {{ $location }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <dl
            x-show="quickFactsVisible()"
            class="grid border-b border-gray-100 dark:border-gray-800 sm:grid-cols-5"
        >
            @foreach ($quickFacts as $fact)
                <div
                    x-show="fieldVisible('{{ $fact['visibility'] }}')"
                    class="border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:border-t-0 sm:border-l first:sm:border-l-0"
                >
                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ $fact['label'] }}
                    </dt>

                    <dd @class([
                        'mt-1 text-sm font-semibold text-gray-950 dark:text-white',
                        'text-gray-400 dark:text-gray-500' => $fact['isEmpty'],
                    ])>
                        {{ $fact['value'] }}
                    </dd>
                </div>
            @endforeach
        </dl>

        @if ($canCustomizeFields)
            <div class="border-b border-gray-100 px-3 py-3 dark:border-gray-800">
                <button
                    type="button"
                    x-on:click="controlsOpen = ! controlsOpen"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:text-gray-200 dark:hover:bg-gray-800"
                >
                    <span>Visible Fields</span>
                    <svg
                        x-bind:class="{ 'rotate-180': controlsOpen }"
                        class="h-4 w-4 text-gray-400 transition-transform"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="controlsOpen"
                    x-collapse
                    class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
                >
                    @foreach ($visibilityControls as $key => $label)
                        <label
                            x-show="filled.{{ $key }}"
                            class="flex items-center gap-2 rounded-md border border-gray-100 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-200"
                        >
                            <input
                                type="checkbox"
                                x-model="visible.{{ $key }}"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-600 dark:bg-gray-900"
                            >
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <nav
            x-show="firstVisibleSection() !== null"
            class="flex gap-1 overflow-x-auto px-3 py-3"
            aria-label="Student profile sections"
        >
            @foreach ($sections as $key => $section)
                <button
                    x-show="sectionVisible('{{ $key }}')"
                    type="button"
                    x-on:click="activeSection = '{{ $key }}'"
                    @class([
                        'shrink-0 rounded-md px-3 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500',
                    ])
                    x-bind:class="activeSection === '{{ $key }}'
                        ? 'bg-amber-500 text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                >
                    {{ $section['label'] }}
                </button>
            @endforeach
        </nav>
    </header>

    <div class="min-h-72 max-h-[55vh] overflow-y-auto pr-1">
        @foreach ($sections as $key => $section)
            <section
                x-show="sectionVisible('{{ $key }}') && activeSection === '{{ $key }}'"
                x-transition.opacity.duration.150ms
                class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">
                        {{ $section['label'] }}
                    </h3>
                </div>

                <dl @class([
                    'grid gap-3 p-5',
                    'lg:grid-cols-2' => ! ($section['wide'] ?? false),
                ])>
                    @foreach ($section['fields'] as $item)
                        <div
                            x-show="fieldVisible('{{ $item['visibility'] }}') && @js(! $item['isEmpty'])"
                            @class([
                                'rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/60',
                                'lg:col-span-2' => ($section['wide'] ?? false),
                            ])
                        >
                            <dt class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                {{ $item['label'] }}
                            </dt>

                            <dd @class([
                                'mt-2 whitespace-pre-line break-words text-sm font-medium text-gray-950 dark:text-white',
                                'text-gray-400 dark:text-gray-500' => $item['isEmpty'],
                            ])>
                                {{ $item['value'] }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endforeach
    </div>
</div>
