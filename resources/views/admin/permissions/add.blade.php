@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php $pageTitle = isset($info) ? __('app.edit') : __('app.add_new'); @endphp

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
                    <label class="form-label d-block mb-3">{{ __('app.permissions') }}</label>

                    <div class="accordion" id="permissionGroupsAccordion">
                        @foreach ($permissionGroups as $group)
                            @php
                                $groupPermissions = $group->permissions->merge($group->children->flatMap->permissions);
                            @endphp
                            @if ($groupPermissions->isNotEmpty())
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#group-{{ $group->id }}">
                                            {{ $group->name_ar ?? $group->name }}
                                            <span class="badge badge-light-primary ms-3">{{ $groupPermissions->count() }}</span>
                                        </button>
                                    </h2>
                                    <div id="group-{{ $group->id }}" class="accordion-collapse collapse" data-bs-parent="#permissionGroupsAccordion">
                                        <div class="accordion-body">
                                            @if ($group->permissions->isNotEmpty())
                                                <div class="row mb-2">
                                                    @foreach ($group->permissions as $permission)
                                                        <div class="col-md-3 mb-2">
                                                            <div class="form-check">
                                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                                                                       id="perm-{{ $permission->id }}"
                                                                       @checked(isset($info) && $info->permissions->contains('name', $permission->name))>
                                                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @foreach ($group->children as $child)
                                                @if ($child->permissions->isNotEmpty())
                                                    <div class="fw-bold text-gray-600 mt-3 mb-2">{{ $child->name_ar ?? $child->name }}</div>
                                                    <div class="row">
                                                        @foreach ($child->permissions as $permission)
                                                            <div class="col-md-3 mb-2">
                                                                <div class="form-check">
                                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="form-check-input"
                                                                           id="perm-{{ $permission->id }}"
                                                                           @checked(isset($info) && $info->permissions->contains('name', $permission->name))>
                                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('permissions.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
