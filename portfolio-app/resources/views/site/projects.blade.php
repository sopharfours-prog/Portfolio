@extends('layouts.site')

@section('title', __('site.meta.projects.title'))
@section('description', __('site.meta.projects.description'))

@section('content')
    @include('site.sections.projects', ['class' => 'section--page projects--all', 'label' => 'site.page.projectsLabel', 'heading' => 'site.page.projectsTitle', 'all' => true, 'titleTag' => 'h1'])
    @include('site.sections.cta')
@endsection
