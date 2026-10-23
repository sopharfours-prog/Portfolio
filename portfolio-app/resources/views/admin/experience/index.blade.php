@extends('admin.layouts.app')
@section('title', __('Experience'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Experience') }}</h1><p>{{ __('Your career timeline.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.experience.create') }}">{{ icon('plus-lg') }}{{ __('Add Experience') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search experience...'),
            'selects' => ['status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Position') }}</th><th>{{ __('Period') }}</th><th>{{ __('Technologies') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'experience') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary>
                                <a href="{{ route('admin.experience.edit', $item) }}"><span class="table__title">{{ $item->tr('position') }}</span>
                                <span class="table__sub" style="display:block">{{ $item->company }}@if ($item->location) · {{ $item->location }}@endif</span></a>
                            </td>
                            <td data-label="{{ __('Period') }}" class="nowrap">{{ $item->start_date->format('Y') }} — {{ $item->is_current ? __('Present') : $item->end_date?->format('Y') }}
                                @if ($item->is_current) <span class="badge badge--success">{{ __('Current') }}</span>@endif</td>
                            <td data-label="{{ __('Technologies') }}"><div class="chips">@foreach (collect($item->technologies)->take(3) as $tech)<span class="chip">{{ $tech }}</span>@endforeach</div></td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.experience.edit', $item), 'delete' => route('admin.experience.destroy', $item), 'confirmTitle' => __('Delete Experience?'), 'name' => $item->tr('position').' — '.$item->company])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('signpost-split') }}<h3>{{ __('No experience found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
