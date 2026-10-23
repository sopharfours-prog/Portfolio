@extends('admin.layouts.app')
@section('title', __('CV'))

@section('content')
    <div class="page-head">
        <div><h1>{{ __('CV') }}</h1><p>{{ __('Upload the CV visitors can download, in each language.') }}</p></div>
        @if (collect($files)->filter()->isNotEmpty() && $showOnSite)
            <div class="page-head__actions"><a class="btn btn--secondary" href="{{ route('cv.download') }}" target="_blank" rel="noopener">{{ icon('box-arrow-up-right') }}{{ __('Test public download') }}</a></div>
        @endif
    </div>

    {{-- Remove forms live outside the upload form (forms can't be nested) --}}
    @foreach ($files as $code => $file)
        @if ($file)
            <form id="cv-remove-{{ $code }}" method="POST" action="{{ route('admin.cv.destroy', $code) }}"
                  data-confirm="{{ __('Remove this CV?') }}" data-confirm-text="{{ __('Visitors will no longer be able to download it.') }}" data-confirm-button="{{ __('Remove') }}">
                @csrf @method('DELETE')
            </form>
        @endif
    @endforeach

    <form method="POST" action="{{ route('admin.cv.update') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="stack" style="max-width:860px">
            <div class="cv-grid">
                @foreach ($files as $code => $file)
                    <section class="card cv-card">
                        <div class="card__head">
                            <h2 class="cv-card__title">@include('site.partials.flag', ['code' => $code]){{ __('site.languages.'.$code) }}</h2>
                            @if ($file)<span class="badge badge--success">{{ __('Uploaded') }}</span>@else<span class="badge badge--warning">{{ __('Not uploaded') }}</span>@endif
                        </div>
                        <div class="card__body">
                            @if ($file)
                                <div class="cv-file">
                                    <span class="icon-tile cv-file__icon">{{ icon('file-earmark-pdf') }}</span>
                                    <span class="cv-file__meta">
                                        <strong class="truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</strong>
                                        <span class="hint">{{ number_format($file['size'] / 1024 / 1024, 2) }} MB · {{ \Illuminate\Support\Carbon::parse($file['uploaded_at'])->diffForHumans() }}</span>
                                    </span>
                                </div>
                                <div class="cv-file__actions">
                                    <a class="btn btn--primary btn--sm" href="{{ route('admin.cv.download', $code) }}">{{ icon('download') }}{{ __('Download') }}</a>
                                    <button type="submit" form="cv-remove-{{ $code }}" class="btn btn--danger-ghost btn--sm">{{ icon('trash') }}{{ __('Remove') }}</button>
                                </div>
                            @endif

                            <label class="dropzone cv-drop" data-file-drop>
                                <input class="sr-only" type="file" name="file_{{ $code }}" accept="application/pdf,.pdf" data-file-input
                                       data-max-bytes="{{ \App\Support\CvFile::maxBytes() }}" data-too-large="{{ __('This file is too large. The maximum is :size MB.', ['size' => round(\App\Support\CvFile::maxBytes() / 1024 / 1024, 1)]) }}">
                                {{ icon('cloud-arrow-up') }}
                                <strong data-file-label data-empty="{{ $file ? __('Replace with a new PDF') : __('Choose a PDF file') }}">{{ $file ? __('Replace with a new PDF') : __('Choose a PDF file') }}</strong>
                                <span>{{ __('PDF only, up to :size MB', ['size' => round(\App\Support\CvFile::maxBytes() / 1024 / 1024, 1)]) }}</span>
                            </label>
                            @error('file_'.$code)<p class="error" style="margin-top:8px">{{ $message }}</p>@enderror
                        </div>
                    </section>
                @endforeach
            </div>

            <section class="card">
                <div class="card__body">
                    <label class="switch"><input type="hidden" name="show_on_site" value="0"><input type="checkbox" name="show_on_site" value="1" @checked(old('show_on_site', $showOnSite))><span class="switch__track"></span><span>{{ __('Show the "Download CV" button on the website') }}</span></label>
                    <p class="hint" style="margin:8px 0 0">{{ __('Visitors get the CV in the language they are viewing; if it is missing, the other language is used.') }}</p>
                </div>
            </section>
        </div>
        <div class="form-actions" style="max-width:860px"><a class="btn btn--ghost" href="{{ route('admin.website.index') }}">{{ __('Cancel') }}</a><button type="submit" class="btn btn--primary">{{ __('Save Changes') }}</button></div>
    </form>
@endsection
