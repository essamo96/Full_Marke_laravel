@extends('layouts.auth')

@section('title', __('Login'))

@section('content')
    <form class="form w-100" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('Sign In') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('Welcome back, please login to your account') }}</div>
        </div>

        <div class="fv-row mb-8">
            <label for="email" class="form-label fs-6 fw-bold">{{ __('Email Address') }}</label>
            <input id="email" type="email"
                   class="form-control bg-transparent @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-3">
            <label for="password" class="form-label fs-6 fw-bold">{{ __('Password') }}</label>
            <input id="password" type="password"
                   class="form-control bg-transparent @error('password') is-invalid @enderror"
                   name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
            <div class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link-primary">{{ __('Forgot Password ?') }}</a>
            @endif
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('Sign In') }}</span>
            </button>
        </div>

        @if (Route::has('register'))
            <div class="text-gray-500 text-center fw-semibold fs-6">
                {{ __('Not a Member yet?') }}
                <a href="{{ route('register') }}" class="link-primary">{{ __('Sign up') }}</a>
            </div>
        @endif
    </form>
@endsection
