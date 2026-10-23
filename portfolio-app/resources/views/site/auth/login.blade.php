@extends('layouts.site')

@section('title', __('site.auth.loginTitle').' — '.($settings['general']['site_name'] ?? 'SOPHAR'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <header class="auth-card__head">
                <span class="auth-card__icon" aria-hidden="true">{{ icon('person') }}</span>
                <h1 class="auth-card__title">{{ __('site.auth.loginTitle') }}</h1>
                <p class="auth-card__text">{{ __('site.auth.loginText') }}</p>
            </header>

            @include('site.auth.social')

            <form class="auth-form" method="POST" action="{{ route('member.login') }}" novalidate>
                @csrf
                @include('site.auth.field', ['name' => 'email', 'type' => 'email', 'autocomplete' => 'email', 'max' => 150, 'autofocus' => true])
                @include('site.auth.field', ['name' => 'password', 'type' => 'password', 'autocomplete' => 'current-password', 'max' => 200])

                <label class="check-x">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>{{ __('site.auth.remember') }}</span>
                </label>

                <button type="submit" class="btn-x btn-x--solid btn-x--block">{{ __('site.auth.submitLogin') }}{{ icon('arrow-right') }}</button>
            </form>

            <p class="auth-card__switch">{{ __('site.auth.noAccount') }} <a href="{{ route('member.register') }}">{{ __('site.auth.register') }}</a></p>
        </div>
    </section>
@endsection
