@extends('layouts.site')

@section('title', __('site.auth.registerTitle').' — '.($settings['general']['site_name'] ?? 'SOPHAR'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <header class="auth-card__head">
                <span class="auth-card__icon" aria-hidden="true">{{ icon('person-plus') }}</span>
                <h1 class="auth-card__title">{{ __('site.auth.registerTitle') }}</h1>
                <p class="auth-card__text">{{ __('site.auth.registerText') }}</p>
            </header>

            @include('site.auth.social')

            <form class="auth-form" method="POST" action="{{ route('member.register') }}" novalidate>
                @csrf
                @include('site.auth.field', ['name' => 'name', 'type' => 'text', 'autocomplete' => 'name', 'max' => 100, 'autofocus' => true])
                @include('site.auth.field', ['name' => 'email', 'type' => 'email', 'autocomplete' => 'email', 'max' => 150])
                @include('site.auth.field', ['name' => 'password', 'type' => 'password', 'autocomplete' => 'new-password', 'max' => 200, 'hint' => __('site.auth.passwordHint')])
                @include('site.auth.field', ['name' => 'password_confirmation', 'label' => 'passwordConfirm', 'type' => 'password', 'autocomplete' => 'new-password', 'max' => 200])

                <button type="submit" class="btn-x btn-x--solid btn-x--block">{{ __('site.auth.submitRegister') }}{{ icon('arrow-right') }}</button>
            </form>

            <p class="auth-card__switch">{{ __('site.auth.haveAccount') }} <a href="{{ route('member.login') }}">{{ __('site.auth.login') }}</a></p>
        </div>
    </section>
@endsection
