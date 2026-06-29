<<<<<<< HEAD
@extends('layouts.site')
=======
@extends('layouts.auth')

@section('title', __('Register'))
>>>>>>> 0c8929078153a1f60d05fab9cd8eee641f10c7af

@section('content')
    <form class="form w-100" method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="text-center mb-11">
            <h1 class="text-dark fw-bolder mb-3">{{ __('Sign Up') }}</h1>
            <div class="text-gray-500 fw-semibold fs-6">{{ __('Create your account to get started') }}</div>
        </div>

        <div class="fv-row mb-8">
            <label for="name" class="form-label fs-6 fw-bold">{{ __('Name') }}</label>
            <input id="name" type="text"
                   class="form-control bg-transparent @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
            <label for="email" class="form-label fs-6 fw-bold">{{ __('Email Address') }}</label>
            <input id="email" type="email"
                   class="form-control bg-transparent @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
            <label for="password" class="form-label fs-6 fw-bold">{{ __('Password') }}</label>
            <input id="password" type="password"
                   class="form-control bg-transparent @error('password') is-invalid @enderror"
                   name="password" required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="fv-row mb-8">
            <label for="password-confirm" class="form-label fs-6 fw-bold">{{ __('Confirm Password') }}</label>
            <input id="password-confirm" type="password"
                   class="form-control bg-transparent"
                   name="password_confirmation" required autocomplete="new-password">
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('Sign Up') }}</span>
            </button>
        </div>

        @if (Route::has('login'))
            <div class="text-gray-500 text-center fw-semibold fs-6">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="link-primary">{{ __('Sign in') }}</a>
            </div>
        @endif
    </form>
@endsection
