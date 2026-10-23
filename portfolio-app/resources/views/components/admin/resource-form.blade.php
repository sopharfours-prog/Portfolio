{{-- Shared page chrome for simple create/edit forms. Slots: $slot (main fields), $aside (sidebar card) --}}
@props(['title', 'index', 'indexLabel', 'action', 'editing', 'current' => null, 'multipart' => false])
<div class="page-head">
    <div>
        <nav class="breadcrumb"><a href="{{ $index }}">{{ $indexLabel }}</a>{{ icon('chevron-right') }}<span>{{ $editing ? ($current ?: __('Edit')) : __('New') }}</span></nav>
        <h1>{{ $title }}</h1>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert--danger" role="alert" style="margin-bottom:20px">
        {{ icon('exclamation-circle') }}<div><strong>{{ __('Please fix the following:') }}</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    </div>
@endif

<form method="POST" action="{{ $action }}" novalidate @if ($multipart) enctype="multipart/form-data" @endif>
    @csrf
    @if ($editing) @method('PUT') @endif
    <div class="grid grid--form">
        <section class="card"><div class="card__body">{{ $slot }}</div></section>
        <aside class="card"><div class="card__body">{{ $aside }}</div></aside>
    </div>
    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ $index }}">{{ __('Cancel') }}</a>
        <button type="submit" class="btn btn--primary">{{ $editing ? __('Save Changes') : __('Create') }}</button>
    </div>
</form>
