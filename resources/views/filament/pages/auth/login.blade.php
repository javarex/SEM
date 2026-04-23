@php
    $appName = config('app.name', 'SEM');
@endphp

<x-filament-panels::page.simple class="sem-login-page">
    <div class="sem-login-shell">
        <div class="sem-login-orb sem-login-orb-one" aria-hidden="true"></div>
        <div class="sem-login-orb sem-login-orb-two" aria-hidden="true"></div>

        <section class="sem-login-hero" aria-labelledby="sem-login-title">
            <div class="sem-login-eyebrow">
                Secure Evaluation Portal
            </div>

            <div class="sem-login-copy">
                <p class="sem-login-kicker">{{ $appName }}</p>

                <h1 id="sem-login-title">
                    Welcome back to a calmer way to manage evaluations.
                </h1>

                <p>
                    Sign in to review student records, manage judging workflows, and keep scholarship evaluation data organized in one focused workspace.
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

        <section class="sem-login-card" aria-labelledby="sem-login-form-title">
            <div class="sem-login-card-header">
                <p>Account access</p>
                <h2 id="sem-login-form-title">Sign in</h2>
                <span>Use your assigned email address and password to continue.</span>
            </div>

            {{ $this->content }}
        </section>
    </div>
</x-filament-panels::page.simple>
