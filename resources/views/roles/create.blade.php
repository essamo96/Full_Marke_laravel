@extends('layouts.site')

@section('title', 'Create Role')

@php($pageTitle = 'Create Role')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h2>New Role</h2>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="fv-row mb-7">
                    <label class="required form-label">Role Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Teacher">
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="fv-row mb-7">
                    <label class="form-label">Permissions</label>
                    <div class="row">
                        @foreach ($permissions as $permission)
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                           value="{{ $permission->name }}" id="permission-{{ $permission->id }}">
                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('roles.index') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
@endsection
