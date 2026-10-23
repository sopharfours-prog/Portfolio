@extends('admin.layouts.app')
@section('title', __('Media Library'))

@section('content')
    <div data-media-library data-upload="{{ route('admin.media.store') }}">
        <div class="page-head">
            <div><h1>{{ __('Media Library') }}</h1><p>{{ __('Images used across your website.') }}</p></div>
        </div>

        <div class="dropzone" data-library-drop role="button" tabindex="0" style="margin-bottom:20px">
            {{ icon('cloud-arrow-up') }}
            <p><strong>{{ __('Drag & drop images here') }}</strong> {{ __('or click to upload') }}</p>
            <p class="hint">JPG, PNG, WebP, SVG · {{ __('max :size MB each', ['size' => \App\Support\ImageUploader::MAX_KB / 1024]) }} · {{ __('JPG and PNG are converted to WebP') }}</p>
            <input type="file" accept=".jpg,.jpeg,.png,.webp,.svg" multiple hidden data-library-file>
        </div>

        <div class="card card--flush">
            @include('admin.partials.filters', [
                'search' => __('Search media...'),
                'selects' => ['type' => [__('File type'), ['image/webp' => 'WebP', 'image/jpeg' => 'JPG', 'image/png' => 'PNG', 'image/svg+xml' => 'SVG']]],
                'sorts' => ['newest' => __('Newest'), 'oldest' => __('Oldest'), 'name' => __('Name'), 'largest' => __('Largest')],
            ])

            @if ($items->isEmpty())
                <div class="empty">{{ icon('images') }}<h3>{{ __('No files found') }}</h3></div>
            @else
                <div class="media-grid">
                    @foreach ($items as $m)
                        <button type="button" class="media-card" data-media="{{ json_encode([
                            'name' => $m->name, 'original_name' => $m->original_name, 'url' => $m->url(), 'mime' => $m->mime_type, 'size' => $m->humanSize(),
                            'dimensions' => $m->dimensions(), 'alt' => $m->alt, 'uploaded' => $m->created_at->translatedFormat('j M Y'),
                            'uploader' => $m->uploader?->name, 'update_url' => route('admin.media.update', $m),
                        ]) }}">
                            <span class="media-card__img {{ $m->mime_type === 'image/svg+xml' ? 'media-card__img--contain' : '' }}"><img src="{{ $m->url() }}" alt="{{ $m->alt }}" loading="lazy"></span>
                            <span class="media-card__body">
                                <span class="media-card__name truncate" style="display:block">{{ $m->name }}</span>
                                <span class="media-card__meta">{{ $m->dimensions() ?? '—' }} · {{ $m->humanSize() }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @endif
            {{ $items->links() }}
        </div>
    </div>
@endsection

@push('modals')
<dialog class="drawer" data-media-drawer aria-label="{{ __('File Information') }}">
    <div class="drawer__preview" data-d-preview></div>
    <div style="padding:20px 24px">
        <div class="row row--between" style="margin-bottom:16px">
            <h2 style="font-size:16px;font-weight:600">{{ __('File Information') }}</h2>
            <button type="button" class="icon-btn" data-drawer-close aria-label="{{ __('Close') }}">{{ icon('x-lg') }}</button>
        </div>

        <form method="POST" data-d-form>
            @csrf @method('PUT')
            <x-admin.field :label="__('Name')"><input class="input" type="text" name="name" required maxlength="120" data-d-name></x-admin.field>
            <x-admin.field :label="__('Alt text')" :hint="__('Describes the image for screen readers and search engines.')"><input class="input" type="text" name="alt" maxlength="255" data-d-alt></x-admin.field>
            <x-admin.field :label="__('URL')">
                <div class="input-group">
                    <input class="input" type="text" readonly data-d-url>
                    <button type="button" class="btn btn--secondary" data-copy data-copied="{{ __('URL copied') }}" data-d-copy>{{ icon('copy') }}{{ __('Copy') }}</button>
                </div>
            </x-admin.field>
            <button type="submit" class="btn btn--primary btn--block" style="margin-top:18px">{{ __('Save Changes') }}</button>
        </form>

        <dl class="meta-list" style="margin:22px 0;padding-top:18px;border-top:1px solid var(--border)">
            <dt>{{ __('Filename') }}</dt><dd data-d-file></dd>
            <dt>{{ __('Type') }}</dt><dd data-d-type></dd>
            <dt>{{ __('Size') }}</dt><dd data-d-size></dd>
            <dt>{{ __('Dimensions') }}</dt><dd data-d-dimensions></dd>
            <dt>{{ __('Upload Date') }}</dt><dd data-d-uploaded></dd>
            <dt>{{ __('Uploaded by') }}</dt><dd data-d-by></dd>
        </dl>

        <form method="POST" data-d-delete data-confirm="{{ __('Delete File?') }}">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn--danger-ghost btn--block">{{ icon('trash3') }}{{ __('Delete file') }}</button>
        </form>
    </div>
</dialog>
@endpush
