@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('permissions.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('permissions.add.submit') }}">
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

                <div class="mb-5">
                    <label class="form-label required">{{ __('app.name') }}</label>
                    <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}" class="form-control" required>
                </div>

                <div class="mb-5">
                    <label class="form-label">{{ __('app.permissions') }}</label>
                    <div class="row">
                        @foreach ($permissions as $permission)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                                           id="perm-{{ $permission->id }}"
                                           @checked(isset($info) && $info->permissions->contains('name', $permission->name))>
                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('permissions.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
