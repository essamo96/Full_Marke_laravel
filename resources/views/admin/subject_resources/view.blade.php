@extends('layouts.admin')

@section('title', __('app.subject_resources'))

@php($pageTitle = __('app.subject_resources'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.subject_resources') }} — {{ $subject->name_ar }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('subjects.view') }}" class="btn btn-light me-2">{{ __('app.cancel') }}</a>
                <a href="{{ route('subject_resources.add', $subject) }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.name') }}</th>
                        <th>Type</th>
                        <th>URL</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($resources as $resource)
                        <tr>
                            <td>{{ $resource->title }}</td>
                            <td><span class="badge badge-light-info">{{ $resource->type }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($resource->url, 40) }}</td>
                            <td>
                                <form action="{{ route('subject_resources.status', $subject) }}" method="POST" class="d-inline ajax-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($resource->id) }}">
                                    <button type="submit" class="badge {{ $resource->is_active ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                                        {{ $resource->is_active ? __('app.active') : __('app.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('subject_resources.edit', [$subject, \Illuminate\Support\Facades\Crypt::encrypt($resource->id)]) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
                                <form action="{{ route('subject_resources.delete', $subject) }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($resource->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
