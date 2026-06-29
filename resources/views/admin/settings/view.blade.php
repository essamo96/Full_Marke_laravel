@extends('layouts.admin')

@section('title', __('app.settings'))

@php($pageTitle = __('app.settings'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.site_name') }}</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.site_email') }}</label>
                        <input type="email" name="site_email" value="{{ old('site_email', $settings['site_email'] ?? '') }}" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.site_phone') }}</label>
                        <input type="text" name="site_phone" value="{{ old('site_phone', $settings['site_phone'] ?? '') }}" class="form-control">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.site_address') }}</label>
                        <textarea name="site_address" class="form-control" rows="1">{{ old('site_address', $settings['site_address'] ?? '') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
            </form>
        </div>
    </div>
@endsection
