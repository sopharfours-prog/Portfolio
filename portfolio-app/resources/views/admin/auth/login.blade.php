@extends('admin.layouts.auth')
@section('title', __('Sign in'))

@section('content')
    @if (session('status'))<p class="alert alert--success" style="margin-bottom:16px">{{ session('status') }}</p>@endif

    <form method="POST" action="{{ route('admin.login') }}" novalidate>
        @csrf
        <x-admin.input :label="__('Email')" name="email" type="email" required autofocus autocomplete="username" />
        <x-admin.input :label="__('Password')" name="password" type="password" required autocomplete="current-password" />
        <button type="submit" class="btn btn--primary btn--block" style="margin-top:22px">{{ __('Sign in') }}</button>
        <div class="auth__links">
            <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> {{ __('Remember me') }}</label>
            <a href="{{ route('admin.password.request') }}">{{ __('Forgot password?') }}</a>
        </div>
    </form>
    <p class="auth__foot"><a href="{{ route('home') }}">← {{ __('Back to website') }}</a></p>
@endsection
