@php
    $user = auth()->user();
    $displayName = $user?->name ?? 'there';
    $today = now();
@endphp

<x-filament-panels::page>
    <div class="sem-dashboard space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(250,204,21,0.20),transparent_28rem),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.16),transparent_26rem)] dark:bg-[radial-gradient(circle_at_top_left,rgba(250,204,21,0.12),transparent_28rem),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.10),transparent_26rem)]"></div>

            <div class="relative grid gap-8 lg:grid-cols-[1fr_18rem] lg:items-end">
                <div class="max-w-3xl">
                    <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-amber-800 ring-1 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-500/30">
                        Evaluation Dashboard
                    </div>

                    <h1 class="mt-5 text-3xl font-black tracking-tight text-gray-950 dark:text-white sm:text-5xl">
                        Welcome back, {{ $displayName }}.
                    </h1>

                    <p class="mt-4 max-w-2xl text-base leading-7 text-gray-600 dark:text-gray-300">
                        Monitor interview progress and daily evaluation activity from one focused workspace. The widgets below keep their existing data logic and update from the current scoring records.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/70 bg-white/75 p-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-gray-950/45">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                        Today
                    </p>

                    <p class="mt-2 text-2xl font-black text-gray-950 dark:text-white">
                        {{ $today->format('M d, Y') }}
                    </p>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $today->format('l') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Workflow</p>
                <p class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Interview tracking</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Scope</p>
                <p class="mt-2 text-lg font-bold text-gray-950 dark:text-white">Student evaluations</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Status</p>
                <p class="mt-2 text-lg font-bold text-emerald-700 dark:text-emerald-300">Live records</p>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-gray-200 bg-gray-50/70 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950/35 sm:p-5">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black tracking-tight text-gray-950 dark:text-white">
                        Interview Metrics
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Existing dashboard widgets, presented with a cleaner layout.
                    </p>
                </div>
            </div>

            {{ $this->content }}
        </section>
    </div>
</x-filament-panels::page>
