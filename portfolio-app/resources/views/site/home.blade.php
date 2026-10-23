@extends('layouts.site')

@section('content')
    @include('site.sections.hero')
    @include('site.sections.about-hero', ['headingTag' => 'h2', 'class' => 'about-hero--section'])
    @include('site.sections.skills')
    @include('site.sections.services')
    @include('site.sections.projects', ['limit' => 6])
    @include('site.sections.experience')
    @include('site.sections.education')
    @include('site.sections.certificates')
    @include('site.sections.testimonials')
    @include('site.sections.contact')
@endsection
