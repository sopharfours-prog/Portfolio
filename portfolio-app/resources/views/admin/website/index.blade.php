@extends('admin.layouts.app')
@section('title', __('Website'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Website') }}</h1><p>{{ __('Edit the main sections of your public website.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--secondary" href="{{ route('home') }}" target="_blank" rel="noopener">{{ icon('box-arrow-up-right') }}{{ __('View website') }}</a></div>
    </div>
    <div class="section-links">
        @foreach ([
            ['admin.website.hero', 'stars', __('Hero'), __('Greeting, name, job title, portrait and buttons.')],
            ['admin.website.about', 'person-lines-fill', __('About'), __('Headline, biography, statistics and social links.')],
            ['admin.website.contact', 'telephone', __('Contact Information'), __('Email, phone, address and social media.')],
            ['admin.cv.edit', 'file-earmark-person', __('CV'), __('Upload the CV visitors can download, in each language.')],
        ] as [$route, $iconName, $title, $text])
            <a class="card section-link" href="{{ route($route) }}">
                <span class="icon-tile">{{ icon($iconName) }}</span>
                <h2>{{ $title }}</h2><p>{{ $text }}</p>
            </a>
        @endforeach
    </div>
@endsection
