@php
    $appName = config('app.name', 'SEM');
@endphp

<x-filament-panels::page.simple class="sem-login-page">
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
                    Structured intake
                </div>

                <div>
                    <span>02</span>
                    Secure access
                </div>

                <div>
                    <span>03</span>
                    Evaluation ready
                </div>
            </div>
        </section>

        <section class="sem-login-card" aria-labelledby="sem-register-form-title">
            <div class="sem-login-card-header">
                <p>Account setup</p>
                <h2 id="sem-register-form-title">Create account</h2>
                <span>Use your assigned details to request access.</span>
            </div>

            {{ $this->content }}

            <div class="sem-auth-switch">
                <span>Already have an account?</span>
                <a href="{{ filament()->getLoginUrl() }}">Sign in</a>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
