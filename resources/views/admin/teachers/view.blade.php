@extends('layouts.admin')

@section('title', __('app.teachers'))

@php($pageTitle = __('app.teachers'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.teachers') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('teachers.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.photo') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.specialty') }}</th>
                        <th>{{ __('app.subjects') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td>
                                @if ($teacher->photo)
                                    <img src="{{ asset('storage/'.$teacher->photo) }}" class="rounded" width="40" height="40" alt="{{ $teacher->name }}">
                                @else
                                    <span class="symbol symbol-40px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($teacher->name, 0, 1) }}</span></span>
                                @endif
                            </td>
                            <td>{{ $teacher->name }}</td>
                            <td>{{ $teacher->email }}</td>
                            <td>{{ $teacher->specialty }}</td>
                            <td>{{ $teacher->subjects_count }}</td>
                            <td>
                                <form action="{{ route('teachers.status') }}" method="POST" class="d-inline ajax-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($teacher->id) }}">
                                    <button type="submit" class="badge {{ $teacher->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                                        {{ $teacher->status ? __('app.active') : __('app.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('teachers.edit', \Illuminate\Support\Facades\Crypt::encrypt($teacher->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
                                <form action="{{ route('teachers.delete') }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($teacher->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $teachers->links() }}
        </div>
    </div>
@endsection
