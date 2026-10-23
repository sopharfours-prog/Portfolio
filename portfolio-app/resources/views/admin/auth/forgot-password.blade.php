@extends('admin.layouts.auth')
@section('title', __('Forgot password'))

@section('content')
    <p class="muted" style="margin-bottom:18px;text-align:center">{{ __('Enter your email and we will send you a link to reset your password.') }}</p>
    @if (session('status'))<p class="alert alert--success" style="margin-bottom:16px">{{ session('status') }}</p>@endif

    <form method="POST" action="{{ route('admin.password.email') }}" novalidate>
        @csrf
        <x-admin.input :label="__('Email')" name="email" type="email" required autofocus autocomplete="username" />
        <button type="submit" class="btn btn--primary btn--block" style="margin-top:22px">{{ __('Send reset link') }}</button>
    </form>
    <p class="auth__foot"><a href="{{ route('admin.login') }}">← {{ __('Back to sign in') }}</a></p>
@endsection
