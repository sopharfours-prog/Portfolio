@extends('admin.layouts.auth')
@section('title', __('Reset password'))

@section('content')
    <form method="POST" action="{{ route('admin.password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-admin.input :label="__('Email')" name="email" type="email" :value="$email" required autocomplete="username" />
        <x-admin.input :label="__('New password')" name="password" type="password" required autocomplete="new-password"
                       :hint="__('At least 10 characters, with upper and lower case letters and a number.')" />
        <x-admin.input :label="__('Confirm password')" name="password_confirmation" type="password" required autocomplete="new-password" />
        <button type="submit" class="btn btn--primary btn--block" style="margin-top:22px">{{ __('Reset password') }}</button>
    </form>
@endsection
