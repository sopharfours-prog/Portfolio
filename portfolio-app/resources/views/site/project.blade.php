@extends('layouts.site')

@section('title', $project->title.' — '.($settings['general']['site_name'] ?? 'SOPHAR'))
@section('description', $project->tr('summary'))
@section('og_image', $project->cover?->path)

@section('content')
    <article class="section section--page">
        <div class="container-xl project-page">
            <a class="link-arrow project-page__back" href="{{ route('projects.index') }}">{{ icon('arrow-left') }} {{ __('site.page.projectsTitle') }}</a>
            @include('site.partials.project-detail', ['heading' => 'h1'])
        </div>
    </article>
    @include('site.sections.cta')
@endsection
