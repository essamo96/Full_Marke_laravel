@extends('layouts.admin-auth')

@section('title', 'Admin Login')

@section('content')
    <form class="form w-100" method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="text-center mb-11">
            <div class="position-relative d-inline-block mb-5" style="width: 260px; height: 200px;">
                <img src="{{ asset('site/images/logo_v2_blue.png') }}" alt="Full Mark Academy" class="theme-light-show h-150px" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <img src="{{ asset('site/images/full_mark_dark.png') }}" alt="Full Mark Academy" class="theme-dark-show h-150px" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="fv-row mb-8">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control bg-transparent" autofocus required>
        </div>

        <div class="fv-row mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control bg-transparent" required>
        </div>

        <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
            <div class="form-check form-check-custom form-check-solid">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
        </div>

        <div class="d-grid mb-10">
            <button type="submit" class="btn btn-primary">Sign In</button>
        </div>
    </form>
@endsection
