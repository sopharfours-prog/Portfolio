{{-- "Continue with Google / Apple" — only providers configured in .env are shown (SocialLoginController::enabled()). --}}
@php $providers = \App\Http\Controllers\Site\Auth\SocialLoginController::enabled(); @endphp
@if ($providers)
    <div class="social-auth">
        @foreach ($providers as $provider)
            <a class="social-auth__btn social-auth__btn--{{ $provider }}" href="{{ route('member.social.redirect', $provider) }}">
                {{ icon($provider) }}<span>{{ __('site.auth.continue'.ucfirst($provider)) }}</span>
            </a>
        @endforeach
    </div>
    <p class="social-auth__or"><span>{{ __('site.auth.orEmail') }}</span></p>
@endif
