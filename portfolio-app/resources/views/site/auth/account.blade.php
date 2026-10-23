@extends('layouts.site')

@section('title', __('site.account.title').' — '.($settings['general']['site_name'] ?? 'SOPHAR'))

@push('head')<meta name="robots" content="noindex">@endpush

@php
    $methods = collect(['google' => 'Google', 'apple' => 'Apple'])->filter(fn ($label, $provider) => filled($member->{$provider.'_id'}));
    $field = fn ($bag, $name) => $errors->getBag($bag)->first($name);
@endphp

@section('content')
    <section class="section section--page account-page">
        <div class="container-xl account">
            <header class="account__head">
                <span class="account__avatar" aria-hidden="true">{{ $member->initials() }}</span>
                <div>
                    <h1 class="account__name">{{ $member->name }}</h1>
                    <p class="account__email">{{ $member->email }}</p>
                </div>
            </header>

            {{-- Registration details --}}
            <section class="account-card">
                <h2 class="account-card__title">{{ icon('person-vcard') }}{{ __('site.account.details') }}</h2>
                <dl class="account-facts">
                    <div><dt>{{ __('site.auth.name') }}</dt><dd>{{ $member->name }}</dd></div>
                    <div><dt>{{ __('site.auth.email') }}</dt><dd>{{ $member->email }}</dd></div>
                    <div>
                        <dt>{{ __('site.account.signedUpWith') }}</dt>
                        <dd class="account-facts__badges">
                            @forelse ($methods as $provider => $label)
                                <span class="account-badge">{{ icon($provider) }}{{ $label }}</span>
                            @empty
                                <span class="account-badge">{{ icon('envelope') }}{{ __('site.auth.email') }}</span>
                            @endforelse
                            @if ($methods->isNotEmpty() && $member->password)<span class="account-badge">{{ icon('key') }}{{ __('site.auth.password') }}</span>@endif
                        </dd>
                    </div>
                    <div><dt>{{ __('site.account.memberSince') }}</dt><dd>{{ $member->created_at->translatedFormat('j F Y') }}</dd></div>
                    <div><dt>{{ __('site.account.lastLogin') }}</dt><dd>{{ $member->last_login_at?->diffForHumans() ?? '—' }}</dd></div>
                    <div><dt>{{ __('site.account.language') }}</dt><dd>{{ $member->locale ? __('site.languages.'.$member->locale) : '—' }}</dd></div>
                </dl>
            </section>

            {{-- Name and language --}}
            <section class="account-card">
                <h2 class="account-card__title">{{ icon('pencil-square') }}{{ __('site.account.edit') }}</h2>
                <form method="POST" action="{{ route('member.account.update') }}" novalidate>
                    @csrf @method('PUT')
                    <div class="account-grid">
                        <div class="field">
                            <label class="field__label" for="acc-name">{{ __('site.auth.name') }}</label>
                            <input class="field__input" id="acc-name" name="name" maxlength="100" required autocomplete="name" value="{{ old('name', $member->name) }}" @if ($field('profile', 'name')) aria-invalid="true" @endif>
                            <p class="field__error">{{ $field('profile', 'name') }}</p>
                        </div>
                        <div class="field">
                            <label class="field__label" for="acc-locale">{{ __('site.account.language') }}</label>
                            <select class="field__input" id="acc-locale" name="locale">
                                @foreach (locales() as $code => $short)
                                    <option value="{{ $code }}" @selected(old('locale', $member->locale ?? app()->getLocale()) === $code)>{{ __('site.languages.'.$code) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-x btn-x--solid btn-x--sm">{{ __('site.account.save') }}</button>
                </form>
            </section>

            {{-- Password --}}
            <section class="account-card">
                <h2 class="account-card__title">{{ icon('shield-lock') }}{{ $member->password ? __('site.account.changePassword') : __('site.account.setPassword') }}</h2>
                @unless ($member->password)<p class="account-card__text">{{ __('site.account.setPasswordText') }}</p>@endunless
                <form method="POST" action="{{ route('member.account.password') }}" novalidate>
                    @csrf @method('PUT')
                    <div class="account-grid">
                        @if ($member->password)
                            <div class="field">
                                <label class="field__label" for="acc-current">{{ __('site.account.currentPassword') }}</label>
                                <input class="field__input" id="acc-current" name="current_password" type="password" required autocomplete="current-password" @if ($field('password', 'current_password')) aria-invalid="true" @endif>
                                <p class="field__error">{{ $field('password', 'current_password') }}</p>
                            </div>
                        @endif
                        <div class="field">
                            <label class="field__label" for="acc-password">{{ __('site.account.newPassword') }}</label>
                            <input class="field__input" id="acc-password" name="password" type="password" required autocomplete="new-password" @if ($field('password', 'password')) aria-invalid="true" @endif>
                            <p class="field__hint">{{ __('site.auth.passwordHint') }}</p>
                            <p class="field__error">{{ $field('password', 'password') }}</p>
                        </div>
                        <div class="field">
                            <label class="field__label" for="acc-password2">{{ __('site.auth.passwordConfirm') }}</label>
                            <input class="field__input" id="acc-password2" name="password_confirmation" type="password" required autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="btn-x btn-x--outline btn-x--sm">{{ $member->password ? __('site.account.changePassword') : __('site.account.setPassword') }}</button>
                </form>
            </section>

            <form method="POST" action="{{ route('member.logout') }}" class="account__logout">
                @csrf
                <button type="submit" class="btn-x btn-x--outline btn-x--sm">{{ icon('box-arrow-right') }}{{ __('site.auth.logout') }}</button>
            </form>
        </div>
    </section>
@endsection
