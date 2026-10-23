<div class="page-head">
    <div>
        <nav class="breadcrumb"><a href="{{ route('admin.website.index') }}">{{ __('Website') }}</a>{{ icon('chevron-right') }}<span>{{ $title }}</span></nav>
        <h1>{{ $title }}</h1>
        <p>{{ $text }}</p>
    </div>
    <div class="page-head__actions">
        <div class="lang-tabs" role="group" aria-label="{{ __('Editing language') }}">
            @foreach (locales() as $code => $short)<button type="button" data-lang-all="{{ $code }}" class="{{ $loop->first ? 'is-active' : '' }}" style="padding:6px 14px;font-size:12px">{{ $short }}</button>@endforeach
        </div>
    </div>
</div>
@if ($errors->any())
    <div class="alert alert--danger" role="alert" style="margin-bottom:20px">
        {{ icon('exclamation-circle') }}<div><strong>{{ __('Please fix the following:') }}</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    </div>
@endif
