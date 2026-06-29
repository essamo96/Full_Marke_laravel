@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('users.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('users.add.submit') }}"
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
                        <label class="form-label required">{{ __('app.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $info->email ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label {{ isset($info) ? '' : 'required' }}">{{ __('app.password') }}</label>
                        <input type="password" name="password" class="form-control" {{ isset($info) ? '' : 'required' }}>
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.role') }}</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- {{ __('app.role') }} --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(isset($info) && $info->roles->contains('name', $role->name))>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.photo') }}</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $info->status ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('users.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
