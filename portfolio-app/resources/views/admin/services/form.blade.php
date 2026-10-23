@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Service') : __('Add Service'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Service') : __('Add Service')" :index="route('admin.services.index')" :index-label="__('Services')"
        :action="$editing ? route('admin.services.update', $item) : route('admin.services.store')" :editing="$editing" :current="$item->tr('title')">
        <div class="fields">
            <x-admin.bilingual :label="__('Service Name')" name="title" :value="$item->title" required :max="120" />
            <x-admin.bilingual :label="__('Description')" name="description" :value="$item->description" textarea :rows="3" required :max="600" />
            <x-admin.bilingual :label="__('Features')" name="features" :value="$item->features" textarea :rows="4" :hint="__('One feature per line.')" />
        </div>
        <x-slot:aside>
            <x-admin.icon-input :label="__('Icon')" :value="$item->icon ?? 'code-slash'" />
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
            <x-admin.status-select :value="$item->status" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
