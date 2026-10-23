@extends('admin.layouts.app')
@section('title', __('Skills'))

@php $categories = collect(\App\Models\Skill::CATEGORIES)->mapWithKeys(fn ($c) => [$c => __('taxonomy.'.$c)])->all(); @endphp

@section('content')
    <div class="page-head">
        <div><h1>{{ __('Skills') }}</h1><p>{{ __('Drag rows to change the order shown on the website.') }}</p></div>
        <div class="page-head__actions"><a class="btn btn--primary" href="{{ route('admin.skills.create') }}">{{ icon('plus-lg') }}{{ __('Add Skill') }}</a></div>
    </div>

    <div class="card card--flush">
        @include('admin.partials.filters', [
            'search' => __('Search skills...'),
            'selects' => ['category' => [__('Category'), $categories], 'status' => [__('Status'), ['published' => __('Published'), 'draft' => __('Draft')]]],
            'sorts' => ['order' => __('Custom order'), 'newest' => __('Newest'), 'oldest' => __('Oldest')],
        ])
        @include('admin.partials.reorder-note')
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th style="width:1%"></th><th>{{ __('Skill') }}</th><th>{{ __('Category') }}</th><th>{{ __('Level') }}</th><th>{{ __('Status') }}</th><th class="col-actions">{{ __('Actions') }}</th></tr></thead>
                <tbody @if ($canReorder) data-sortable="{{ route('admin.reorder', 'skills') }}" data-start="{{ ($items->currentPage() - 1) * $items->perPage() }}" @endif>
                    @forelse ($items as $item)
                        <tr data-id="{{ $item->id }}">
                            <td class="col-drag"><button type="button" class="drag-handle" data-drag aria-label="{{ __('Drag to reorder') }}" @disabled(! $canReorder)>{{ icon('grip-vertical') }}</button></td>
                            <td data-primary><a class="cell-media" href="{{ route('admin.skills.edit', $item) }}"><span class="icon-tile">{{ icon($item->icon) }}</span><span class="table__title">{{ $item->name }}</span></a></td>
                            <td data-label="{{ __('Category') }}">{{ __('taxonomy.'.$item->category) }}</td>
                            <td data-label="{{ __('Level') }}">
                                @if (! is_null($item->level))
                                    <div class="level"><div class="level__bar"><span style="width:{{ $item->level }}%"></span></div><span class="num">{{ $item->level }}%</span></div>
                                @else <span class="muted">—</span> @endif
                            </td>
                            <td data-label="{{ __('Status') }}"><x-admin.badge :status="$item->status" /></td>
                            <td class="col-actions">@include('admin.partials.row-actions', ['edit' => route('admin.skills.edit', $item), 'delete' => route('admin.skills.destroy', $item), 'confirmTitle' => __('Delete Skill?'), 'name' => $item->name])</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">{{ icon('lightning-charge') }}<h3>{{ __('No skills found') }}</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
@endsection
