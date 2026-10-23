@extends('admin.layouts.app')
@section('title', __('About'))

@php $stats = array_pad(old('stats', $about['stats'] ?? []), 4, []); @endphp

@section('content')
    @include('admin.partials.website-head', ['title' => __('About'), 'text' => __('Tell visitors who you are.')])

    <form method="POST" action="{{ route('admin.website.about') }}" novalidate data-preview-form>
        @csrf @method('PUT')
        <div class="grid grid--form">
            <div class="stack">
                <section class="card">
                    <div class="card__head"><div><h2>{{ __('About page') }}</h2><p>{{ __('The large heading area at the top of the About page.') }}</p></div></div>
                    <div class="card__body"><div class="fields">
                        <x-admin.image-input :label="__('Profile Image')" name="image" :value="$about['image'] ?? null" full
                            :hint="__('A tall portrait works best (about 760×1400). The top of the photo is always kept.')" />
                        <x-admin.image-input :label="__('Education photo')" name="education_image" :value="$about['education_image'] ?? null" full
                            :hint="__('Shown beside the education list, e.g. a graduation photo. A tall photo with a plain or transparent background works best. Empty: the profile image is used.')" />
                        <x-admin.bilingual :label="__('Heading')" name="page_title" :value="$about['page_title'] ?? []" required :max="60" :full="false" />
                        <x-admin.bilingual :label="__('Heading (highlighted part)')" name="page_title_accent" :value="$about['page_title_accent'] ?? []" :max="60" :full="false" :hint="__('Shown in cyan after the heading.')" />
                        <x-admin.bilingual :label="__('Subheading')" name="subtitle" :value="$about['subtitle'] ?? []" :max="120" :hint="__('Your job title.')" />
                        <x-admin.bilingual :label="__('Button text')" name="cta_label" :value="$about['cta_label'] ?? []" :max="40" :full="false" :hint="__('Leave empty to hide the button.')" />
                        <x-admin.input :label="__('Button URL')" name="cta_url" :value="$about['cta_url'] ?? '#contact'" :hint="__('#section, /page or https://…')" />
                    </div></div>
                </section>

                <section class="card">
                    <div class="card__head"><div><h2>{{ __('Content') }}</h2><p>{{ __('Introduction, biography and statistics appear on the About page and in the About section of the home page.') }}</p></div></div>
                    <div class="card__body"><div class="fields">
                        <x-admin.bilingual :label="__('Headline')" name="headline" :value="$about['headline'] ?? []" required :max="200" />
                        <x-admin.bilingual :label="__('Introduction')" name="introduction" :value="$about['introduction'] ?? []" textarea :rows="3" required />
                        <x-admin.bilingual :label="__('Biography')" name="biography" :value="$about['biography'] ?? []" textarea :rows="4" />
                        <x-admin.bilingual :label="__('Block 1 title')" name="focus_title" :value="$about['focus_title'] ?? []" :full="false" />
                        <x-admin.bilingual :label="__('Block 2 title')" name="philosophy_title" :value="$about['philosophy_title'] ?? []" :full="false" />
                        <x-admin.bilingual :label="__('Block 1 text')" name="focus" :value="$about['focus'] ?? []" textarea :rows="3" :full="false" />
                        <x-admin.bilingual :label="__('Block 2 text')" name="philosophy" :value="$about['philosophy'] ?? []" textarea :rows="3" :full="false" />
                        <x-admin.bilingual :label="__('Skills Summary')" name="skills_summary" :value="$about['skills_summary'] ?? []" textarea :rows="2" :hint="__('Shown under the Skills heading.')" />
                    </div></div>
                </section>

                <section class="card">
                    <div class="card__head"><div><h2>{{ __('Statistics') }}</h2><p>{{ __('Leave a row empty to remove it.') }}</p></div></div>
                    <div class="card__body">
                        <div class="stat-row muted" style="font-size:12px"><span>{{ __('Number') }}</span><span>{{ __('Suffix') }}</span><span>{{ __('Label') }} (LA)</span><span>{{ __('Label') }} (EN)</span></div>
                        @foreach ($stats as $i => $stat)
                            <div class="stat-row" data-stat-row>
                                <input class="input" type="number" min="0" name="stats[{{ $i }}][value]" value="{{ $stat['value'] ?? '' }}" aria-label="{{ __('Number') }}" data-stat-value>
                                <input class="input" type="text" maxlength="5" name="stats[{{ $i }}][suffix]" value="{{ $stat['suffix'] ?? '' }}" placeholder="+" aria-label="{{ __('Suffix') }}" data-stat-suffix>
                                <input class="input" type="text" maxlength="60" name="stats[{{ $i }}][label][lo]" value="{{ $stat['label']['lo'] ?? '' }}" aria-label="{{ __('Label') }} LA" data-stat-label="lo">
                                <input class="input" type="text" maxlength="60" name="stats[{{ $i }}][label][en]" value="{{ $stat['label']['en'] ?? '' }}" aria-label="{{ __('Label') }} EN" data-stat-label="en">
                            </div>
                        @endforeach
                    </div>
                </section>

                @include('admin.partials.socials-card')
            </div>

            <aside class="preview" data-preview>
                <div class="card">
                    <div class="card__head">
                        <h2>{{ __('Live Preview') }}</h2>
                        <div class="lang-tabs">@foreach (locales() as $code => $short)<button type="button" data-preview-lang="{{ $code }}" class="{{ $loop->first ? 'is-active' : '' }}">{{ $short }}</button>@endforeach</div>
                    </div>
                    <div class="card__body">
                        <div class="preview__frame">
                            <p class="preview__eyebrow">01 — {{ __('About') }}</p>
                            <img class="preview__img" data-p-image src="{{ media_url($about['image'] ?? null) }}" alt="" @if (empty($about['image'])) hidden @endif>
                            <p class="preview__headline"><span data-p="page_title"></span><span data-p="page_title_accent" style="color:#00afc4"></span></p>
                            <p class="preview__sub" data-p="subtitle"></p>
                            <p class="preview__intro" data-p="introduction"></p>
                            <p class="preview__bio" data-p="biography"></p>
                            <div class="preview__stats" data-p-stats></div>
                            <p><span class="preview__btn" data-p="cta_label"></span></p>
                        </div>
                        <p class="hint" style="margin-top:10px">{{ __('Updates as you type. Save to publish.') }}</p>
                    </div>
                </div>
            </aside>
        </div>
        <div class="form-actions"><a class="btn btn--ghost" href="{{ route('admin.website.index') }}">{{ __('Cancel') }}</a><button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button></div>
    </form>
@endsection
