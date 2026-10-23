@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Education') : __('Add Education'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Education') : __('Add Education')" :index="route('admin.education.index')" :index-label="__('Education')"
        :action="$editing ? route('admin.education.update', $item) : route('admin.education.store')" :editing="$editing" :current="$item->institution">
        <div class="fields">
            <x-admin.input :label="__('Institution')" name="institution" :value="$item->institution" required maxlength="150" />
            <x-admin.input :label="__('Location')" name="location" :value="$item->location" maxlength="150" />
            <x-admin.bilingual :label="__('Degree')" name="degree" :value="$item->degree" required :max="150" :hint="__('e.g. Bachelor of Computer Science, or the name of a certificate.')" />
            <x-admin.bilingual :label="__('Field of study')" name="field" :value="$item->field" :max="150" />
            <x-admin.input :label="__('Start Date')" name="start_date" type="date" :value="$item->start_date?->format('Y-m-d')" required />
            <x-admin.input :label="__('End Date')" name="end_date" type="date" :value="$item->end_date?->format('Y-m-d')" data-end-date />
            <div class="field field--full">
                <label class="switch"><input type="checkbox" name="is_current" value="1" data-current-toggle @checked(old('is_current', $item->is_current))><span class="switch__track"></span><span>{{ __('Currently studying') }}</span></label>
            </div>
            <x-admin.bilingual :label="__('Description')" name="description" :value="$item->description" textarea :rows="4" />
        </div>
        <x-slot:aside>
            <x-admin.image-input :label="__('Image')" name="image" :value="$item->image" :hint="__('School logo, certificate or diploma. Shown on the website.')" />
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
            <x-admin.status-select :value="$item->status" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
