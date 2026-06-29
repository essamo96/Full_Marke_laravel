@extends('layouts.admin')

@section('title', __('app.payment_methods'))

@php($pageTitle = __('app.payment_methods'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.payment_methods') }}</h2></div>
            <div class="card-toolbar">
                <a href="{{ route('payment_methods.add') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> {{ __('app.add_new') }}
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.details') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($methods as $method)
                        <tr>
                            <td>{{ $method->name_ar }} / {{ $method->name_en }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($method->details, 60) }}</td>
                            <td>
                                <form action="{{ route('payment_methods.status') }}" method="POST" class="d-inline ajax-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($method->id) }}">
                                    <button type="submit" class="badge {{ $method->is_active ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                                        {{ $method->is_active ? __('app.active') : __('app.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('payment_methods.edit', \Illuminate\Support\Facades\Crypt::encrypt($method->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
                                <form action="{{ route('payment_methods.delete') }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($method->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $methods->links() }}
        </div>
    </div>
@endsection
