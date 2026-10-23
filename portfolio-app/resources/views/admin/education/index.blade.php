@extends('admin.layouts.app')
@section('title', __('Education'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Education') }}</h1><p>{{ __('Your studies and certificates.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.education.create') }}">{{ icon('plus-lg') }}{{ __('Add Education') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search education...'),
            'selects' => ['status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Degree') }}</th><th>{{ __('Field of study') }}</th><th>{{ __('Period') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'education') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary>
                                <a href="{{ route('admin.education.edit', $item) }}"><span class="table__title">{{ $item->tr('degree') }}</span>
                                <span class="table__sub" style="display:block">{{ $item->institution }}@if ($item->location) · {{ $item->location }}@endif</span></a>
                            </td>
                            <td data-label="{{ __('Field of study') }}">{{ $item->tr('field') ?? '—' }}</td>
                            <td data-label="{{ __('Period') }}" class="nowrap">{{ $item->start_date->format('Y') }} — {{ $item->is_current ? __('Present') : $item->end_date?->format('Y') }}
                                @if ($item->is_current) <span class="badge badge--success">{{ __('Current') }}</span>@endif</td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.education.edit', $item), 'delete' => route('admin.education.destroy', $item), 'confirmTitle' => __('Delete Education?'), 'name' => $item->tr('degree').' — '.$item->institution])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('mortarboard') }}<h3>{{ __('No education found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
