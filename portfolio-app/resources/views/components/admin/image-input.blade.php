{{-- Single image chosen from (or uploaded to) the Media Library. Stores the media path. --}}
@props(['label', 'name', 'value' => null, 'hint' => null, 'full' => false])
@php $path = old($name, $value); @endphp
<x-admin.field :label="$label" :name="$name" :hint="$hint" :full="$full">
    <div class="image-input" data-image-input>
        <input type="hidden" name="{{ $name }}" value="{{ $path }}" data-image-value>
        <span class="image-input__preview" data-image-preview>
            @if ($path)<img src="{{ media_url($path) }}" alt="">@else{{ icon('image') }}@endif
        </span>
        <div class="image-input__actions">
            <button type="button" class="btn btn--secondary btn--sm" data-image-choose>{{ icon('images') }}{{ __('Choose image') }}</button>
            <button type="button" class="btn btn--danger-ghost btn--sm" data-image-remove @if (! $path) hidden @endif>{{ __('Remove') }}</button>
        </div>
    </div>
</x-admin.field>
