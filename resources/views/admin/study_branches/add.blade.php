@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('study_branches.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('study_branches.add.submit') }}">
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
                        <label class="form-label required">{{ __('app.study_branch_name_ar') }}</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.study_branch_name_en') }}</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $info->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('study_branches.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
