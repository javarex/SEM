<div class="fi-simple-page">
    {{ $this->content }}

    <div class="login-register-link">
        <span>Already have an account?</span>
        <a href="{{ filament()->getLoginUrl() }}" class="login-register-anchor">Sign in</a>
    </div>

    @if (! $this instanceof \Filament\Tables\Contracts\HasTable)
        <x-filament-actions::modals />
    @endif
</div>
