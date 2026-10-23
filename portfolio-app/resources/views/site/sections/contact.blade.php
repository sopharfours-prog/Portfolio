{{--
    Contact: heading with accent word, two short headed paragraphs, contact details with icons and
    social links on the left; the message form on the right. Texts: Admin → Website → Contact Information.
--}}
@php
    $contact = $settings['contact'] ?? [];
    // On the Contact page this section is the first thing on screen: show it at once (no scroll reveal)
    $reveal = ($titleTag ?? 'h2') === 'h1' ? '' : 'data-animate=fade-up';
    $field = fn ($name) => $errors->first($name);
@endphp
<section class="section section--contact {{ $class ?? '' }}" id="contact" data-section>
    <div class="container-xl">
        <div class="row gy-5 gx-lg-5">
            <div class="col-lg-6">
                <{{ $titleTag ?? 'h2' }} class="contact__title" {{ $reveal }}>{{ __('site.contact.title') }} <span class="contact__title-accent">{{ __('site.contact.titleAccent') }}</span></{{ $titleTag ?? 'h2' }}>

                <div class="contact__blurbs" {{ $reveal }}>
                    <h3 class="contact__subtitle">{{ tr($contact['intro_title'] ?? null) ?: __('site.contact.introTitle') }}</h3>
                    <p class="contact__text">{{ tr($contact['intro'] ?? null) ?: __('site.contact.text') }}</p>
                    <h3 class="contact__subtitle">{{ tr($contact['why_title'] ?? null) ?: __('site.contact.whyTitle') }}</h3>
                    <p class="contact__text">{{ tr($contact['why_text'] ?? null) ?: __('site.contact.whyText') }}</p>
                </div>

                <ul class="contact-details" {{ $reveal }}>
                    @if (! empty($contact['email']))
                        <li>{{ icon('send-fill') }}<span>{{ __('site.contact.email') }}: <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></span></li>
                    @endif
                    @if (! empty($contact['phone']))
                        <li>{{ icon('telephone-fill') }}<span>{{ __('site.contact.phone') }}: <a href="tel:{{ preg_replace('/[^\d+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></span></li>
                    @endif
                    @if ($address = tr($contact['address'] ?? null))
                        <li>{{ icon('geo-alt-fill') }}<span>{{ __('site.contact.location') }}: {{ $address }}</span></li>
                    @endif
                </ul>
                <ul class="social-list social-list--icons contact__socials">@include('site.partials.socials')</ul>
            </div>

            <div class="col-lg-6">
                <form class="contact-form" action="{{ route('contact.store') }}" method="POST" data-contact-form novalidate @if ($reveal) data-animate="fade-left" @endif>
                    @csrf
                    @if (session('contact_success'))<p class="form-status is-success" role="status">{{ session('contact_success') }}</p>@endif
                    <div class="hp-field" aria-hidden="true"><label for="cf-website">Website</label><input id="cf-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    @foreach ([
                        ['name', 'text', 'name', 100, true],
                        ['phone', 'tel', 'tel', 40, false],
                        ['email', 'email', 'email', 150, true],
                        ['subject', 'text', 'off', 150, true],
                    ] as [$name, $type, $autocomplete, $max, $required])
                        <div class="field">
                            <label class="visually-hidden" for="cf-{{ $name }}">{{ __('site.contact.'.$name) }}</label>
                            <input class="field__input" id="cf-{{ $name }}" name="{{ $name }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif maxlength="{{ $max }}"
                                   value="{{ old($name) }}" placeholder="{{ __('site.contact.'.$name.'Ph') }}" aria-describedby="cf-{{ $name }}-error" @if ($field($name)) aria-invalid="true" @endif>
                            <p class="field__error" id="cf-{{ $name }}-error">{{ $field($name) }}</p>
                        </div>
                    @endforeach
                    <div class="field">
                        <label class="visually-hidden" for="cf-message">{{ __('site.contact.message') }}</label>
                        <textarea class="field__input field__input--area" id="cf-message" name="message" rows="7" required maxlength="3000"
                                  placeholder="{{ __('site.contact.messagePh') }}" aria-describedby="cf-message-error" @if ($field('message')) aria-invalid="true" @endif>{{ old('message') }}</textarea>
                        <p class="field__error" id="cf-message-error">{{ $field('message') }}</p>
                    </div>
                    <button type="submit" class="contact-form__send" data-submit>
                        <span data-submit-label>{{ __('site.contact.send') }}</span>
                    </button>
                    <p class="form-status" role="status" data-form-status></p>
                    @if ($reply = tr($contact['reply_note'] ?? null))<p class="form-note">{{ $reply }}</p>@endif
                </form>
            </div>
        </div>
    </div>
</section>
