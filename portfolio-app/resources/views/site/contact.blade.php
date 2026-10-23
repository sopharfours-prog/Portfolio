@extends('layouts.site')

@section('title', __('site.meta.contact.title'))
@section('description', __('site.meta.contact.description'))

@section('content')
    @include('site.sections.contact', ['class' => 'section--page', 'titleTag' => 'h1'])
@endsection
