@extends('layouts.admin')

@section('title', __('app.permissions'))

@php($pageTitle = __('app.permissions'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.permissions') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('permissions.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.permissions') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions->pluck('name')->implode(', ') }}</td>
                            <td class="text-end">
                                <a href="{{ route('permissions.edit', \Illuminate\Support\Facades\Crypt::encrypt($role->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
                                <form action="{{ route('permissions.delete') }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($role->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $roles->links() }}
        </div>
    </div>
@endsection
