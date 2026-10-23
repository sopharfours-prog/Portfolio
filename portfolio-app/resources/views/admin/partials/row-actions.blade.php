{{-- Edit / Delete (plus optional extra links) for a table row --}}
<div class="row" style="gap:4px;justify-content:flex-end">
    {{ $extra ?? '' }}
    <a class="btn btn--ghost btn--sm" href="{{ $edit }}">{{ icon('pencil') }}<span>{{ __('Edit') }}</span></a>
    <form method="POST" action="{{ $delete }}" data-confirm="{{ $confirmTitle }}" data-confirm-text="{{ __('Are you sure you want to delete ":name"?', ['name' => $name]) }}">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn--danger-ghost btn--sm">{{ icon('trash3') }}<span>{{ __('Delete') }}</span></button>
    </form>
</div>
