@extends('admin.layouts.app')
@section('title', __('Testimonials'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Testimonials') }}</h1><p>{{ __('What clients say about your work.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.testimonials.create') }}">{{ icon('plus-lg') }}{{ __('Add Testimonial') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search testimonials...'),
            'selects' => ['status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft'), 'hidden' => __('Hidden')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Client') }}</th><th>{{ __('Message') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'testimonials') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary>
                                <a class="cell-media" href="{{ route('admin.testimonials.edit', $item) }}">
                                    @if ($item->photoUrl())<img class="avatar avatar--lg" src="{{ $item->photoUrl() }}" alt="" loading="lazy">@else<span class="avatar avatar--lg avatar--soft">{{ $item->initials() }}</span>@endif
                                    <span><span class="table__title">{{ $item->client_name }}</span><span class="table__sub" style="display:block">{{ collect([$item->tr('position'), $item->company])->filter()->implode(', ') }}</span></span>
                                </a>
                            </td>
                            <td data-label="{{ __('Message') }}"><span class="muted truncate" style="display:block;max-width:320px">{{ $item->tr('message') }}</span></td>
                            <td data-label="{{ __('Rating') }}"><span class="stars" aria-label="{{ $item->rating }} / 5">@for ($i = 1; $i <= 5; $i++)<span class="{{ $i > $item->rating ? 'is-off' : '' }}">★</span>@endfor</span></td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.testimonials.edit', $item), 'delete' => route('admin.testimonials.destroy', $item), 'confirmTitle' => __('Delete Testimonial?'), 'name' => $item->client_name])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('chat-quote') }}<h3>{{ __('No testimonials found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
