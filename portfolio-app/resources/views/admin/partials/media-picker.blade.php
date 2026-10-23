{{-- "Choose from library" dialog used by image inputs and the project gallery --}}
<dialog class="modal modal--lg" data-media-picker data-url="{{ route('admin.media.index') }}" data-upload="{{ route('admin.media.store') }}" aria-labelledby="picker-title">
    <div class="modal__head">
        <h2 id="picker-title">{{ __('Media Library') }}</h2>
        <button type="button" class="icon-btn" data-picker-close aria-label="{{ __('Close') }}">{{ icon('x-lg') }}</button>
    </div>
    <div class="toolbar" style="padding:16px 24px">
        <div class="search toolbar__grow">{{ icon('search') }}<input class="input" type="search" placeholder="{{ __('Search media...') }}" data-picker-search></div>
        <label class="btn btn--secondary">{{ icon('upload') }}{{ __('Upload') }}<input type="file" accept=".jpg,.jpeg,.png,.webp,.svg" multiple hidden data-picker-upload></label>
    </div>
    <div class="media-grid" style="max-height:52vh;overflow-y:auto" data-picker-grid></div>
    <div class="modal__foot">
        <button type="button" class="btn btn--ghost" data-picker-more hidden>{{ __('Load more') }}</button>
        <span style="flex:1"></span>
        <button type="button" class="btn btn--secondary" data-picker-close>{{ __('Cancel') }}</button>
        <button type="button" class="btn btn--primary" data-picker-confirm disabled>{{ __('Select') }}</button>
    </div>
</dialog>
