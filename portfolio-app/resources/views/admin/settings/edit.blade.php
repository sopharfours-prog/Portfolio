@extends('admin.layouts.app')
@section('title', __('Settings'))

@php
    $tabs = ['general' => __('General'), 'seo' => __('SEO'), 'social' => __('Social'), 'appearance' => __('Appearance'), 'language' => __('Language'), 'mail' => __('Email')];
    $general = $settings['general'] ?? [];
    $seo = $settings['seo'] ?? [];
    $language = $settings['language'] ?? ['enabled' => ['lo', 'en'], 'default' => 'lo'];
@endphp

@section('content')
    <div class="page-head"><div><h1>{{ __('Settings') }}</h1><p>{{ __('Website configuration.') }}</p></div></div>

    <nav class="tabs tabs--line" style="margin-bottom:24px" aria-label="{{ __('Settings') }}">
        @foreach ($tabs as $key => $text)
            <a class="tab {{ $tab === $key ? 'is-active' : '' }}" href="{{ route('admin.settings.edit', ['tab' => $key]) }}" @if ($tab === $key) aria-current="page" @endif>{{ $text }}</a>
        @endforeach
    </nav>

    @if ($errors->any())
        <div class="alert alert--danger" role="alert" style="margin-bottom:20px;max-width:860px">
            {{ icon('exclamation-circle') }}<div><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" novalidate style="max-width:860px">
        @csrf @method('PUT')
        <input type="hidden" name="tab" value="{{ $tab }}">

        @if ($tab === 'general')
            <section class="card"><div class="card__body"><div class="fields">
                <x-admin.input :label="__('Website Name')" name="site_name" :value="$general['site_name'] ?? ''" required maxlength="60" full />
                <x-admin.bilingual :label="__('Tagline')" name="tagline" :value="$general['tagline'] ?? []" :max="120" />
                <x-admin.bilingual :label="__('Description')" name="description" :value="$general['description'] ?? []" textarea :rows="3" :max="300" />
                <x-admin.image-input :label="__('Logo')" name="logo" :value="$general['logo'] ?? null" :hint="__('Square image. Shown in the website header and the admin sidebar.')" />
                <x-admin.image-input :label="__('Favicon')" name="favicon" :value="$general['favicon'] ?? null" :hint="__('Square PNG or SVG, at least 64×64.')" />
            </div></div></section>
        @elseif ($tab === 'seo')
            <section class="card"><div class="card__body"><div class="fields">
                <x-admin.bilingual :label="__('Meta Title')" name="meta_title" :value="$seo['meta_title'] ?? []" required :max="70" :hint="__('Up to 70 characters. Shown in the browser tab and search results.')" />
                <x-admin.bilingual :label="__('Meta Description')" name="meta_description" :value="$seo['meta_description'] ?? []" textarea :rows="3" required :max="170" :hint="__('Up to 170 characters.')" />
                <x-admin.input :label="__('Keywords')" name="keywords" :value="$seo['keywords'] ?? ''" :hint="__('Separate with commas.')" full />
                <x-admin.image-input :label="__('OG Image')" name="og_image" :value="$seo['og_image'] ?? null" :hint="__('1200×630. Shown when the site is shared on social media.')" full />
            </div></div></section>
        @elseif ($tab === 'social')
            @include('admin.partials.socials-card')
        @elseif ($tab === 'appearance')
            <section class="card">
                <div class="card__head"><div><h2>{{ __('Website theme') }}</h2><p>{{ __('The default for visitors who have not chosen a theme themselves.') }}</p></div></div>
                <div class="card__body">
                    <div class="theme-options">
                        @foreach (['light' => ['sun', __('Light')], 'dark' => ['moon-stars', __('Dark')], 'system' => ['circle-half', __('System')]] as $value => [$iconName, $text])
                            <label class="theme-option"><input type="radio" name="default_theme" value="{{ $value }}" @checked(old('default_theme', $settings['appearance']['default_theme'] ?? 'system') === $value)>{{ icon($iconName) }}<span>{{ $text }}</span></label>
                        @endforeach
                    </div>
                </div>
            </section>
            @php $colors = \App\Support\ButtonColors::current(); @endphp
            <section class="card mt">
                <div class="card__head"><div><h2>{{ __('Button colours') }}</h2><p>{{ __('Colours of the website\'s buttons, links and highlights. Hover shades, borders and glows follow automatically.') }}</p></div></div>
                <div class="card__body">
                    <div class="color-fields" data-color-preview-scope>
                        @foreach ([
                            'primary' => [__('Main colour'), __('Solid buttons such as Send and Read more, links, icons, back to top.')],
                            'secondary' => [__('Gradient — middle'), __('Middle of gradient buttons (e.g. Sign up), focus rings and borders.')],
                            'tertiary' => [__('Gradient — end'), __('End of gradient buttons and title highlights.')],
                        ] as $key => [$label, $hint])
                            <div class="color-field">
                                <label class="label" for="color-{{ $key }}">{{ $label }}</label>
                                <div class="color-field__control">
                                    <input type="color" id="color-{{ $key }}" name="colors[{{ $key }}]" value="{{ old('colors.'.$key, $colors[$key]) }}" data-color-input="{{ $key }}">
                                    <code data-color-value="{{ $key }}">{{ old('colors.'.$key, $colors[$key]) }}</code>
                                </div>
                                <p class="hint">{{ $hint }}</p>
                                @error('colors.'.$key)<p class="error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="color-preview" data-color-preview style="--p: {{ $colors['primary'] }}; --s: {{ $colors['secondary'] }}; --t: {{ $colors['tertiary'] }}">
                        <span class="color-preview__label">{{ __('Preview') }}</span>
                        <span class="color-preview__btn color-preview__btn--gradient">{{ __('site.auth.register') }}</span>
                        <span class="color-preview__btn color-preview__btn--solid">{{ __('site.contact.send') }}</span>
                        <span class="color-preview__btn color-preview__btn--outline">{{ __('site.services.readMore') }}</span>
                        <span class="color-preview__link">{{ __('site.nav.contact') }}</span>
                    </div>
                    <label class="check" style="margin-top:16px"><input type="checkbox" name="reset_colors" value="1"> {{ __('Reset to the default colours') }}</label>
                </div>
            </section>
            <section class="card mt">
                <div class="card__head"><div><h2>{{ __('Admin theme') }}</h2><p>{{ __('Saved in this browser only.') }}</p></div></div>
                <div class="card__body">
                    <div class="theme-options">
                        @foreach (['light' => ['sun', __('Light')], 'dark' => ['moon-stars', __('Dark')], 'system' => ['circle-half', __('System')]] as $value => [$iconName, $text])
                            <label class="theme-option"><input type="radio" name="_admin_theme" value="{{ $value }}" data-theme-choice>{{ icon($iconName) }}<span>{{ $text }}</span></label>
                        @endforeach
                    </div>
                </div>
            </section>
        @elseif ($tab === 'language')
            <section class="card"><div class="card__body">
                <x-admin.field :label="__('Website languages')" name="enabled" :hint="__('Visitors can switch between the enabled languages.')">
                    <div class="row row--wrap" style="gap:20px">
                        @foreach (['lo' => __('Lao'), 'en' => __('English')] as $code => $text)
                            <label class="check"><input type="checkbox" name="enabled[]" value="{{ $code }}" @checked(in_array($code, old('enabled', $language['enabled'] ?? []), true))> {{ $text }}</label>
                        @endforeach
                    </div>
                </x-admin.field>
                <x-admin.select :label="__('Default Language')" name="default" :value="$language['default'] ?? 'lo'" :options="['lo' => __('Lao'), 'en' => __('English')]" />
            </div></section>
        @elseif ($tab === 'mail')
            @php
                $mail = $settings['mail'] ?? [];
                $live = ! \App\Support\MailSettings::isTestMode();
            @endphp
            <div class="alert {{ $live ? 'alert--success' : 'alert--warning' }}" style="margin-bottom:20px">
                {{ icon($live ? 'check-circle' : 'exclamation-triangle') }}
                <div>{{ $live ? __('Emails are sent through :host.', ['host' => config('mail.mailers.smtp.host')]) : __('Test mode: emails are written to storage/logs/laravel.log and not delivered. Choose "Send real emails" below to deliver them.') }}</div>
            </div>
            <section class="card" data-mail-settings>
                <div class="card__head"><div><h2>{{ __('Outgoing email') }}</h2><p>{{ __('Used for replies to messages, password resets and other emails from the website.') }}</p></div></div>
                <div class="card__body"><div class="fields">
                    <x-admin.field :label="__('Mode')" name="mode" full>
                        <div class="row row--wrap" style="gap:20px">
                            <label class="check"><input type="radio" name="mode" value="log" @checked(old('mode', $mail['mode'] ?? 'log') === 'log')> {{ __('Test mode (log only)') }}</label>
                            <label class="check"><input type="radio" name="mode" value="smtp" @checked(old('mode', $mail['mode'] ?? 'log') === 'smtp')> {{ __('Send real emails (SMTP)') }}</label>
                        </div>
                    </x-admin.field>
                    <div class="field field--full">
                        <button type="button" class="btn btn--secondary btn--sm" data-mail-preset="gmail">{{ icon('google') }}{{ __('Use Gmail settings') }}</button>
                        <p class="hint" style="margin-top:6px">{{ __('Gmail needs an App Password (Google Account → Security → 2-Step Verification → App passwords), not your normal password.') }}</p>
                    </div>
                    <x-admin.input :label="__('SMTP server')" name="host" :value="$mail['host'] ?? ''" placeholder="smtp.gmail.com" />
                    <x-admin.input :label="__('Port')" name="port" type="number" :value="$mail['port'] ?? 587" />
                    <x-admin.select :label="__('Security')" name="encryption" :value="$mail['encryption'] ?? 'tls'" :options="['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => __('None')]" />
                    <x-admin.input :label="__('Username')" name="username" :value="$mail['username'] ?? ''" autocomplete="off" />
                    <x-admin.input :label="__('Password')" name="password" type="password" autocomplete="new-password"
                        :hint="\App\Support\MailSettings::hasPassword() ? __('A password is saved. Leave empty to keep it.') : __('Stored encrypted.')" />
                    <x-admin.input :label="__('Sender email')" name="from_address" type="email" :value="$mail['from_address'] ?? ''" :hint="__('Usually the same as the username.')" />
                    <x-admin.input :label="__('Sender name')" name="from_name" :value="$mail['from_name'] ?? ($settings['general']['site_name'] ?? '')" />
                </div></div>
            </section>
        @endif

        <div class="form-actions">
            @if ($tab === 'mail')<button type="submit" form="mail-test" class="btn btn--secondary">{{ icon('send') }}{{ __('Send test email') }}</button>@endif
            <button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
    @if ($tab === 'mail')
        {{-- Separate form (forms can't be nested); sends with the SAVED settings --}}
        <form id="mail-test" method="POST" action="{{ route('admin.settings.mail-test') }}">@csrf</form>
    @endif
@endsection
