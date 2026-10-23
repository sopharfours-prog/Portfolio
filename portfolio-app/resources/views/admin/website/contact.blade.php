@extends('admin.layouts.app')
@section('title', __('Contact Information'))

@section('content')
    @include('admin.partials.website-head', ['title' => __('Contact Information'), 'text' => __('How visitors can reach you.')])

    <form method="POST" action="{{ route('admin.website.contact') }}" novalidate>
        @csrf @method('PUT')
        <div class="stack" style="max-width:860px">
            <section class="card">
                <div class="card__head"><h2>{{ __('Details') }}</h2></div>
                <div class="card__body"><div class="fields">
                    <x-admin.input :label="__('Email')" name="email" type="email" :value="$contact['email'] ?? ''" required />
                    <x-admin.input :label="__('Phone')" name="phone" type="tel" :value="$contact['phone'] ?? ''" />
                    <x-admin.bilingual :label="__('Address')" name="address" :value="$contact['address'] ?? []" />
                    <x-admin.bilingual :label="__('Note under the form')" name="reply_note" :value="$contact['reply_note'] ?? []" :hint="__('e.g. how quickly you usually reply.')" />
                    <x-admin.bilingual :label="__('First heading')" name="intro_title" :value="$contact['intro_title'] ?? []" :max="120"
                        :hint="__('Shown above the first paragraph in the Contact section. Empty: the default text is shown.')" />
                    <x-admin.bilingual :label="__('Contact section text')" name="intro" :value="$contact['intro'] ?? []" textarea :rows="2" :max="300"
                        :hint="__('The first paragraph in the Contact section. Empty: the default text is shown.')" />
                    <x-admin.bilingual :label="__('Second heading')" name="why_title" :value="$contact['why_title'] ?? []" :max="120"
                        :hint="__('e.g. why clients should work with you. Empty: the default text is shown.')" />
                    <x-admin.bilingual :label="__('Second paragraph')" name="why_text" :value="$contact['why_text'] ?? []" textarea :rows="2" :max="400" />
                    <x-admin.bilingual :label="__('Footer text')" name="footer_text" :value="$contact['footer_text'] ?? []" :max="120"
                        :hint="__('The footer line, shown before your name (e.g. “Made with care by”). Empty: the default text is shown.')" />
                </div></div>
            </section>
            @include('admin.partials.socials-card')
        </div>
        <div class="form-actions" style="max-width:860px"><a class="btn btn--ghost" href="{{ route('admin.website.index') }}">{{ __('Cancel') }}</a><button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button></div>
    </form>
@endsection
