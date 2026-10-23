@extends('admin.layouts.app')
@php $editing = $item->exists; @endphp
@section('title', $editing ? __('Edit Skill') : __('Add Skill'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit Skill') : __('Add Skill')" :index="route('admin.skills.index')" :index-label="__('Skills')"
        :action="$editing ? route('admin.skills.update', $item) : route('admin.skills.store')" :editing="$editing" :current="$item->name">
        <div class="fields">
            <x-admin.input :label="__('Skill Name')" name="name" :value="$item->name" required maxlength="60" />
            <x-admin.bilingual :label="__('Display name')" name="label" :value="$item->label" :max="60"
                :hint="__('Optional. Use it for professional skills, e.g. ຄວາມຄິດສ້າງສັນ / Creativity. Technical names like HTML can stay empty.')" />
            <x-admin.select :label="__('Category')" name="category" :value="$item->category ?? 'frontend'" required
                :options="collect(\App\Models\Skill::CATEGORIES)->mapWithKeys(fn ($c) => [$c => __('taxonomy.'.$c)])->all()" />
            <x-admin.input :label="__('Level')" name="level" type="number" min="0" max="100" :value="$item->level" :hint="__('0–100. Professional skills show it as a ring, technical skills as a bar.')" />
            <x-admin.input :label="__('Order')" name="sort_order" type="number" min="0" :value="$item->sort_order ?? 0" />
        </div>
        <x-slot:aside>
            <x-admin.icon-input :label="__('Icon')" :value="$item->icon ?? 'code-slash'" />
            <x-admin.status-select :value="$item->status" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
