@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Experience') : __('Add Experience'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Experience') : __('Add Experience')" :index="route('admin.experience.index')" :index-label="__('Experience')"
        :action="$editing ? route('admin.experience.update', $item) : route('admin.experience.store')" :editing="$editing" :current="$item->company">
        <div class="fields">
            <x-admin.input :label="__('Company')" name="company" :value="$item->company" required maxlength="150" />
            <x-admin.input :label="__('Location')" name="location" :value="$item->location" maxlength="150" />
            <x-admin.bilingual :label="__('Position')" name="position" :value="$item->position" required :max="150" />
            <x-admin.input :label="__('Start Date')" name="start_date" type="date" :value="$item->start_date?->format('Y-m-d')" required />
            <x-admin.input :label="__('End Date')" name="end_date" type="date" :value="$item->end_date?->format('Y-m-d')" data-end-date />
            <div class="field field--full">
                <label class="switch"><input type="checkbox" name="is_current" value="1" data-current-toggle @checked(old('is_current', $item->is_current))><span class="switch__track"></span><span>{{ __('Current Position') }}</span></label>
            </div>
            <x-admin.bilingual :label="__('Description')" name="description" :value="$item->description" textarea :rows="4" />
            <x-admin.input :label="__('Technologies')" name="technologies" :value="implode(', ', (array) old('technologies', $item->technologies ?? []))" :hint="__('Separate with commas.')" full />
        </div>
        <x-slot:aside>
            <x-admin.image-input :label="__('Logo')" name="image" :value="$item->image" :hint="__('Company logo. Shown in a circle on the website.')" />
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
            <x-admin.status-select :value="$item->status" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
