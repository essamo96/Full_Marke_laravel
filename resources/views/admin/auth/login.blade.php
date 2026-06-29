@extends('layouts.admin-auth')

@section('title', 'Admin Login')

@section('content')
    <form class="form w-100" method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="text-center mb-11">
            <img src="{{ asset('site/images/img/logo_backup.png') }}" alt="Full Mark Academy" class="h-60px mb-5">
            <h1 class="text-gray-900 fw-bolder mb-3">Admin Sign In</h1>
            <div class="text-gray-500 fw-semibold fs-6">Full Mark Academy — Administration Panel</div>
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
