@props(['label', 'name' => 'icon', 'value' => 'code-slash'])
@php $current = old($name, $value); @endphp
<x-admin.field :label="$label" :name="$name" required>
    <div data-icon-input data-url="{{ route('admin.icons') }}" data-base="{{ url('admin/icons') }}">
        <div class="input-group">
            <span class="icon-tile" data-icon-preview>{{ icon($current) }}</span>
            <input class="input" id="f-{{ $name }}" type="text" name="{{ $name }}" value="{{ $current }}" required pattern="[a-z0-9-]+" autocomplete="off"
                   placeholder="{{ __('Search icons...') }}" data-icon-value @error($name) aria-invalid="true" @enderror>
        </div>
        <div class="icon-picker" data-icon-grid hidden></div>
    </div>
    <p class="hint">{{ __('Type to search 2,000+ Bootstrap Icons, then click one.') }}</p>
</x-admin.field>
