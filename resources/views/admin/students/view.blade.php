@extends('layouts.admin')

@section('title', __('app.students'))

@php($pageTitle = __('app.students'))

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><h2>{{ __('app.students') }}</h2></div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th>{{ __('app.photo') }}</th>
                        <th>{{ __('app.name') }}</th>
                        <th>{{ __('app.email') }}</th>
                        <th>{{ __('app.phone') }}</th>
                        <th>{{ __('app.branches') }}</th>
                        <th>{{ app()->getLocale() === 'ar' ? 'عدد التسجيلات' : 'Registrations' }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach ($students as $student)
                        <tr>
                            <td>
                                @if ($student->image)
                                    <img src="{{ asset('storage/'.$student->image) }}" class="rounded" width="40" height="40" alt="{{ $student->full_name_en }}">
                                @else
                                    <span class="symbol symbol-40px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($student->full_name_en, 0, 1) }}</span></span>
                                @endif
                            </td>
                            <td>{{ $student->full_name_en }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->phone }}</td>
                            <td>{{ $student->branch?->name }}</td>
                            <td>{{ $student->registrations_count }}</td>
                            <td>
                                <form action="{{ route('students.status') }}" method="POST" class="d-inline ajax-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($student->id) }}">
                                    <button type="submit" class="badge {{ $student->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                                        {{ $student->status ? __('app.active') : __('app.inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('students.show', \Illuminate\Support\Facades\Crypt::encrypt($student->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.details') }}</a>
                                <form action="{{ route('students.delete') }}" method="POST" class="d-inline ajax-delete-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($student->id) }}">
                                    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $students->links() }}
        </div>
    </div>
@endsection
