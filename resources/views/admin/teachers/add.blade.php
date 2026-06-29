@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('teachers.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('teachers.add.submit') }}"
                  enctype="multipart/form-data">
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
                        <label class="form-label required">{{ __('app.name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $info->email ?? '') }}" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.phone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', $info->phone ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label {{ isset($info) ? '' : 'required' }}">{{ __('app.password') }}</label>
                        <input type="password" name="password" class="form-control" {{ isset($info) ? '' : 'required' }}>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.specialty') }}</label>
                        <input type="text" name="specialty" value="{{ old('specialty', $info->specialty ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.photo') }}</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label">{{ __('app.bio') }}</label>
                    <textarea name="bio" class="form-control" rows="3">{{ old('bio', $info->bio ?? '') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-5">
                        <label class="form-label">{{ __('app.subjects') }}</label>
                        <select name="subject_ids[]" class="form-select" multiple size="5">
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                        @selected(isset($info) && $info->subjects->contains('id', $subject->id))>
                                    {{ $subject->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $info->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('teachers.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
