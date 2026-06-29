@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('payment_methods.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('payment_methods.add.submit') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.name') }} (AR)</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.name') }} (EN)</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}" class="form-control">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label">{{ __('app.details') }}</label>
                    <textarea name="details" class="form-control" rows="3">{{ old('details', $info->details ?? '') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.sort') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $info->sort_order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('payment_methods.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
