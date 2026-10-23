@extends('layouts.site')

@section('title', __('site.meta.about.title'))
@section('description', tr($settings['about']['introduction'] ?? null) ?: __('site.meta.about.description'))
@section('og_image', $settings['about']['image'] ?? '')

@section('content')
    @include('site.sections.about-hero')
    @include('site.sections.skills')
    @include('site.sections.experience')
    @include('site.sections.education')
    @include('site.sections.certificates')
    @include('site.sections.testimonials')
    @include('site.sections.contact')
@endsection
