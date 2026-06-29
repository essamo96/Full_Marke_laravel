@extends('layouts.admin')

@section('title', __('app.groups'))

@php($pageTitle = __('app.groups'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.groups') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('groups.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.group_name') }}</th>
                        <th>{{ __('app.subject') }}</th>
                        <th>{{ __('app.teacher') }}</th>
                        <th>{{ __('app.days') }}</th>
                        <th>{{ __('app.current_count') }}/{{ __('app.max_capacity') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($groups as $group)
                        <tr>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->subject?->name_ar }}</td>
                            <td>{{ $group->teacher?->name ?? '-' }}</td>
                            <td>{{ implode(', ', $group->days ?? []) }} ({{ $group->start_time }}-{{ $group->end_time }})</td>
                            <td>{{ $group->current_count }}/{{ $group->max_capacity }}</td>
                            <td>
                                <form action="{{ route('groups.status') }}" method="POST" class="d-inline ajax-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($group->id) }}">
                                    <button type="submit" class="badge {{ $group->is_active ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                                        {{ $group->is_active ? __('app.active') : __('app.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('groups.edit', \Illuminate\Support\Facades\Crypt::encrypt($group->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
                                <form action="{{ route('groups.delete') }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($group->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $groups->links() }}
        </div>
    </div>
@endsection
