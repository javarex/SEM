@php
    use Filament\Support\Enums\Width;

    $appName = config('app.name', 'SEM');
    $livewire ??= null;

    $renderHookScopes = $livewire?->getRenderHookScopes();
    $maxContentWidth ??= (filament()->getSimplePageMaxContentWidth() ?? Width::Large);

    if (is_string($maxContentWidth)) {
        $maxContentWidth = Width::tryFrom($maxContentWidth) ?? $maxContentWidth;
    }
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @props([
        'after' => null,
        'heading' => null,
        'subheading' => null,
    ])

    <div class="fi-simple-layout">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, scopes: $renderHookScopes) }}

        @if (($hasTopbar ?? true) && filament()->auth()->check())
            <div class="fi-simple-layout-header">
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                @if (filament()->hasUserMenu())
                    @livewire(Filament\Livewire\SimpleUserMenu::class)
                @endif
            </div>
        @endif

        <div class="fi-simple-main-ctn">
            <main
                @class([
                    'fi-simple-main',
                    'px-0',
                    'py-0',
                    'm-0',
                    'rounded-none',
                    'border-none',
                    'bg-transparent',
                    'ring-0',
                    'shadow-none',
                    ($maxContentWidth instanceof Width) ? "fi-width-{$maxContentWidth->value}" : $maxContentWidth,
                ])
                id="ddo-login"
            >
                <div class="sem-login-page">
                    <div class="sem-login-shell">
                        <div class="sem-login-orb sem-login-orb-one" aria-hidden="true"></div>
                        <div class="sem-login-orb sem-login-orb-two" aria-hidden="true"></div>

                        <section class="sem-login-hero" aria-labelledby="sem-register-title">
                            <div class="sem-login-eyebrow">
                                Secure Evaluation Portal
                            </div>

                            <div class="sem-login-copy">
                                <p class="sem-login-kicker">{{ $appName }}</p>

                                <h1 id="sem-register-title">
                                    Create your account for focused evaluation management.
                                </h1>

                                <p>
                                    Register to access student records, coordinate judging workflows, and keep scholarship evaluation data organized in one workspace.
                                </p>
                            </div>

                            <div class="sem-login-highlights" aria-label="Platform highlights">
                                <div>
                                    <span>01</span>
                                    Centralized records
                                </div>

                                <div>
                                    <span>02</span>
                                    Role-aware access
                                </div>

                                <div>
                                    <span>03</span>
                                    Streamlined scoring
                                </div>
                            </div>
                        </section>

                        <section class="sem-login-card" aria-labelledby="sem-register-form-title">
                            <div class="sem-login-card-header">
                                <p>Account setup</p>
                                <h2 id="sem-register-form-title">Create account</h2>
                                <span>Use your assigned details to request access.</span>
                            </div>

                            {{ $slot }}
                        </section>
                    </div>
                </div>
            </main>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $renderHookScopes) }}

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_END, scopes: $renderHookScopes) }}
    </div>
</x-filament-panels::layout.base>
