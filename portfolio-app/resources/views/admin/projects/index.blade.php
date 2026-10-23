@extends('admin.layouts.app')
@section('title', __('Projects'))

@php
    $total = $counts->sum();
    $categories = collect(\App\Models\Project::CATEGORIES)->mapWithKeys(fn ($c) => [$c => __('taxonomy.'.$c)])->all();
@endphp

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Projects') }}</h1><p>{{ __('Manage your portfolio projects.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.projects.create') }}">{{ icon('plus-lg') }}{{ __('Add Project') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search projects...'),
            'tabs' => ['status', [
                '' => [__('All'), $total],
                'published' => [__('Published'), $counts['published'] ?? 0],
                'draft' => [__('Draft'), $counts['draft'] ?? 0],
                'archived' => [__('Archived'), $counts['archived'] ?? 0],
            ]],
            'selects' => ['category' => [__('Category'), $categories]],
            'sorts' => ['newest' => __('Newest'), 'oldest' => __('Oldest'), 'title' => __('Name'), 'views' => __('Most viewed'), 'order' => __('Website order')],
        ])

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Project') }}</th><th>{{ __('Category') }}</th><th>{{ __('Technologies') }}</th>
                        <th>{{ __('Status') }}</th><th>{{ __('Created') }}</th><th class="col-actions">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td data-primary>
                                <a class="cell-media" href="{{ route('admin.projects.edit', $project) }}">
                                    @if ($project->coverUrl())<img class="thumb" src="{{ $project->coverUrl() }}" alt="" loading="lazy">@else<span class="thumb"></span>@endif
                                    <span style="min-width:0">
                                        <span class="table__title">{{ $project->title }}@if ($project->featured) <span title="{{ __('Featured') }}">★</span>@endif</span>
                                        <span class="table__sub truncate" style="display:block;max-width:260px">{{ $project->client ?: '/'.$project->slug }}</span>
                                    </span>
                                </a>
                            </td>
                            <td data-label="{{ __('Category') }}">{{ __('taxonomy.'.$project->category) }}</td>
                            <td data-label="{{ __('Technologies') }}">
                                <div class="chips">
                                    @foreach (collect($project->technologies)->take(3) as $tech)<span class="chip">{{ $tech }}</span>@endforeach
                                    @if (count($project->technologies ?? []) > 3)<span class="chip">+{{ count($project->technologies) - 3 }}</span>@endif
                                </div>
                            </td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$project->status" /></td>
                            <td data-label="{{ __('Created') }}" class="nowrap muted">{{ $project->created_at->translatedFormat('j M Y') }}</td>
                            <td class="col-actions">
                                <div class="row" style="gap:4px;justify-content:flex-end">
                                    <a class="btn btn--ghost btn--sm" href="{{ route('admin.projects.edit', $project) }}">{{ icon('pencil') }}<span>{{ __('Edit') }}</span></a>
                                    <div class="dropdown" data-dropdown>
                                        <button type="button" class="icon-btn" data-dropdown-toggle aria-haspopup="true" aria-expanded="false" aria-label="{{ __('More actions') }}">{{ icon('three-dots') }}</button>
                                        <div class="dropdown__menu">
                                            @if ($project->status === 'published')
                                                <a class="dropdown__item" href="{{ route('projects.show', $project) }}" target="_blank" rel="noopener">{{ icon('eye') }}{{ __('View') }}</a>
                                            @endif
                                            <form method="POST" action="{{ route('admin.projects.duplicate', $project) }}">@csrf
                                                <button type="submit" class="dropdown__item">{{ icon('copy') }}{{ __('Duplicate') }}</button>
                                            </form>
                                            <div class="dropdown__sep"></div>
                                            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" data-confirm="{{ __('Delete Project?') }}"
                                                  data-confirm-text="{{ __('Are you sure you want to delete ":name"?', ['name' => $project->title]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown__item dropdown__item--danger">{{ icon('trash3') }}{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('collection') }}<h3>{{ __('No projects found') }}</h3><p>{{ __('Try a different search or filter, or add a new project.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $projects->links() }}
    </div>
@endsection
