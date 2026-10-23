@extends('admin.layouts.app')
@section('title', __('Certificates'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Certificates') }}</h1><p>{{ __('Certificates shown as a gallery on the website.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.certificates.create') }}">{{ icon('plus-lg') }}{{ __('Add Certificate') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search certificates...'),
            'selects' => ['status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Certificate') }}</th><th>{{ __('Issued by') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'certificates') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary>
                                <a class="cell-media" href="{{ route('admin.certificates.edit', $item) }}">
                                    <img class="cert-thumb" src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                                    <span class="table__title">{{ $item->tr('title') }}</span>
                                </a>
                            </td>
                            <td data-label="{{ __('Issued by') }}"><span class="muted">{{ $item->issuer ?: '—' }}</span></td>
                            <td data-label="{{ __('Date') }}"><span class="muted">{{ $item->issued_on?->format('Y-m-d') ?? '—' }}</span></td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.certificates.edit', $item), 'delete' => route('admin.certificates.destroy', $item), 'confirmTitle' => __('Delete Certificate?'), 'name' => $item->tr('title')])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('patch-check') }}<h3>{{ __('No certificates yet') }}</h3><p>{{ __('Add your first certificate to show the gallery on the website.') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
