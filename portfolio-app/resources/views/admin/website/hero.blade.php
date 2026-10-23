@extends('admin.layouts.app')
@section('title', __('Hero'))

@section('content')
    @include('admin.partials.website-head', ['title' => __('Hero'), 'text' => __('The first thing visitors see.')])

    <form method="POST" action="{{ route('admin.website.hero') }}" novalidate>
        @csrf @method('PUT')
        <div class="grid grid--form">
            <div class="stack">
                <section class="card">
                    <div class="card__head"><h2>{{ __('Text') }}</h2></div>
                    <div class="card__body"><div class="fields">
                        <x-admin.bilingual :label="__('Small label')" name="label" :value="$hero['label'] ?? []" :hint="__('e.g. availability. Leave empty to hide.')" />
                        <x-admin.bilingual :label="__('Greeting')" name="greeting" :value="$hero['greeting'] ?? []" required :full="false" />
                        <x-admin.input :label="__('Name')" name="name" :value="$hero['name'] ?? ''" required maxlength="40" />
                        <x-admin.bilingual :label="__('Job Title')" name="title" :value="$hero['title'] ?? []" required :full="false" />
                        <x-admin.bilingual :label="__('Job Title (second line)')" name="subtitle" :value="$hero['subtitle'] ?? []" :full="false" />
                        <x-admin.bilingual :label="__('Description')" name="description" :value="$hero['description'] ?? []" textarea :rows="3" required :max="500" />
                    </div></div>
                </section>
                <section class="card">
                    <div class="card__head"><h2>{{ __('Buttons') }}</h2></div>
                    <div class="card__body"><div class="fields">
                        <x-admin.bilingual :label="__('Primary Button')" name="primary_label" :value="$hero['primary_label'] ?? []" :full="false" />
                        <x-admin.input :label="__('Primary Button URL')" name="primary_url" :value="$hero['primary_url'] ?? '#projects'" :hint="__('#section, /page or https://…')" />
                        <x-admin.bilingual :label="__('Secondary Button')" name="secondary_label" :value="$hero['secondary_label'] ?? []" :full="false" />
                        <x-admin.input :label="__('Secondary Button URL')" name="secondary_url" :value="$hero['secondary_url'] ?? '#contact'" />
                    </div></div>
                </section>
            </div>
            <aside class="card">
                <div class="card__head"><h2>{{ __('Hero Image') }}</h2></div>
                <div class="card__body">
                    <x-admin.image-input :label="__('Portrait')" name="image" :value="$hero['image'] ?? null" :hint="__('Portrait 4:5, at least 1000×1250.')" />
                    <x-admin.bilingual :label="__('Badge on image')" name="badge" :value="$hero['badge'] ?? []" :full="false" />
                    <x-admin.bilingual :label="__('Location')" name="location" :value="$hero['location'] ?? []" :full="false" />
                </div>
            </aside>
        </div>
        <div class="form-actions"><a class="btn btn--ghost" href="{{ route('admin.website.index') }}">{{ __('Cancel') }}</a><button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button></div>
    </form>
@endsection
