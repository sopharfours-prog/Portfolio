@extends('admin.layouts.app')
@section('title', __('Services'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Services') }}</h1><p>{{ __('What you offer, shown on the home page.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.services.create') }}">{{ icon('plus-lg') }}{{ __('Add Service') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search services...'),
            'selects' => ['status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Service') }}</th><th>{{ __('Description') }}</th><th>{{ __('Order') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'services') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary>
                                <a class="cell-media" href="{{ route('admin.services.edit', $item) }}">
                                    <span class="icon-tile">{{ icon($item->icon) }}</span>
                                    <span><span class="table__title">{{ $item->tr('title') }}</span><span class="table__sub" style="display:block">{{ $item->title['en'] ?? '' }}</span></span>
                                </a>
                            </td>
                            <td data-label="{{ __('Description') }}"><span class="muted truncate" style="display:block;max-width:340px">{{ $item->tr('description') }}</span></td>
                            <td data-label="{{ __('Order') }}" class="num">{{ $item->sort_order + 1 }}</td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.services.edit', $item), 'delete' => route('admin.services.destroy', $item), 'confirmTitle' => __('Delete Service?'), 'name' => $item->tr('title')])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('briefcase') }}<h3>{{ __('No services found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
