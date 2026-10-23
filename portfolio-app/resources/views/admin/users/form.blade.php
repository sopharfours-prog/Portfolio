@extends('admin.layouts.app')
@php $editing = $user->exists; @endphp
@section('title', $editing ? __('Edit User') : __('Add User'))

@section('content')
    <x-admin.resource-form :title="$editing ? __('Edit User') : __('Add User')" :index="route('admin.users.index')" :index-label="__('Users')"
        :action="$editing ? route('admin.users.update', $user) : route('admin.users.store')" :editing="$editing" :current="$user->name">
        <div class="fields">
            <x-admin.input :label="__('Name')" name="name" :value="$user->name" required maxlength="100" />
            <x-admin.input :label="__('Email')" name="email" type="email" :value="$user->email" required />
            <x-admin.input :label="$editing ? __('New password') : __('Password')" name="password" type="password" :required="! $editing" autocomplete="new-password"
                :hint="$editing ? __('Leave empty to keep the current password.') : __('At least 10 characters, with upper and lower case letters and a number.')" />
            <x-admin.input :label="__('Confirm password')" name="password_confirmation" type="password" :required="! $editing" autocomplete="new-password" />
        </div>
        <x-slot:aside>
            <x-admin.select :label="__('Role')" name="role" :value="$user->role" required :options="['editor' => __('Editor'), 'super_admin' => __('Super Admin')]"
                :hint="__('Editors manage content, messages and media. Super Admins can also change website content, settings and users.')" />
            <x-admin.select :label="__('Admin language')" name="locale" :value="$user->locale" :options="['lo' => __('Lao'), 'en' => __('English')]" />
        </x-slot:aside>
    </x-admin.resource-form>
@endsection
