@php
    use Illuminate\Support\Str;

    $dateCards = $this->getDateCards();
    $selectedScores = $this->getSelectedInterviewScores();
@endphp

<x-filament-widgets::widget>
    <section class="space-y-4">
        <div>
            <h3 class="text-base font-bold text-gray-950 dark:text-white">
                Interviews Per Date
            </h3>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Unique interviewed students grouped by scoring date. Click a date to view the student list.
            </p>
        </div>

        @if (empty($dateCards))
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                No interview records have been submitted yet.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($dateCards as $card)
                    <button
                        type="button"
                        wire:click="showInterviewedStudents(@js($card['date']))"
                        wire:loading.attr="disabled"
                        class="fi-wi-stats-overview-stat group cursor-pointer text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950"
                    >
                        <div class="fi-wi-stats-overview-stat-content">
                            <div class="fi-wi-stats-overview-stat-label-ctn">
                                <span class="fi-wi-stats-overview-stat-label">
                                    {{ $card['label'] }}
                                </span>
                            </div>

                            <div class="fi-wi-stats-overview-stat-value">
                                {{ $card['count'] }}
                            </div>

                            <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-amber-700 opacity-80 transition group-hover:opacity-100 dark:text-amber-300">
                                View students
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    @if ($isModalOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 p-4 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="daily-interviews-modal-title"
            x-data
            x-on:keydown.escape.window="$wire.closeInterviewedStudentsModal()"
        >
            <div class="max-h-[85vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-700">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-6 dark:border-gray-800">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">
                            Interviewed Students
                        </p>

                        <h3 id="daily-interviews-modal-title" class="mt-2 text-2xl font-black tracking-tight text-gray-950 dark:text-white">
                            {{ $this->getSelectedDateLabel() }}
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $selectedScores->count() }} unique {{ Str::plural('student', $selectedScores->count()) }} interviewed on this date.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeInterviewedStudentsModal"
                        class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        aria-label="Close modal"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>

                <div class="max-h-[62vh] overflow-y-auto p-6">
                    @if ($selectedScores->isEmpty())
                        <div class="rounded-2xl border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                No students found.
                            </p>

                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                There are no interview records for the selected date.
                            </p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                            <div class="grid grid-cols-12 bg-gray-50 px-4 py-3 text-xs font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-800/70 dark:text-gray-400">
                                <div class="col-span-2">ID</div>
                                <div class="col-span-5">Student</div>
                                <div class="col-span-3">Municipality</div>
                                <div class="col-span-2 text-right">Scored At</div>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($selectedScores as $score)
                                    @php
                                        $student = $score->student;
                                        $studentName = $student?->getRawOriginal('fullname')
                                            ?: trim(($student?->first_name ?? '').' '.($student?->last_name ?? ''));
                                    @endphp

                                    <div class="grid grid-cols-12 items-center gap-3 px-4 py-4 text-sm">
                                        <div class="col-span-2 font-mono text-gray-500 dark:text-gray-400">
                                            {{ $student?->id ?? $score->student_id }}
                                        </div>

                                        <div class="col-span-5 min-w-0">
                                            <p class="truncate font-semibold text-gray-950 dark:text-white">
                                                {{ $studentName ?: 'Student record unavailable' }}
                                            </p>

                                            @if ($student?->type)
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $student->type }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="col-span-3 text-gray-600 dark:text-gray-300">
                                            {{ $student?->municipality ?: 'N/A' }}
                                        </div>

                                        <div class="col-span-2 text-right text-gray-500 dark:text-gray-400">
                                            {{ $score->created_at->format('g:i A') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-950/60">
                    <button
                        type="button"
                        wire:click="closeInterviewedStudentsModal"
                        class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
