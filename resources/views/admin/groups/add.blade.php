@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('groups.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('groups.add.submit') }}">
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
                        <label class="form-label required">{{ __('app.subject') }}</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- {{ __('app.subject') }} --</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $info->subject_id ?? '') == $subject->id)>{{ $subject->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.teacher') }}</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">-- {{ __('app.teacher') }} --</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $info->teacher_id ?? '') == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.group_name') }}</label>
                        <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.days') }}</label>
                        @php($selectedDays = old('days', $info->days ?? []))
                        <div class="d-flex flex-wrap gap-3">
                            @foreach (['sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة'] as $key => $label)
                                <div class="form-check">
                                    <input type="checkbox" name="days[]" value="{{ $key }}" class="form-check-input" id="day_{{ $key }}" @checked(in_array($key, $selectedDays))>
                                    <label class="form-check-label" for="day_{{ $key }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-5">
                        <label class="form-label required">{{ __('app.start_time') }}</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $info->start_time ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label required">{{ __('app.end_time') }}</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $info->end_time ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label required">{{ __('app.max_capacity') }}</label>
                        <input type="number" min="1" name="max_capacity" value="{{ old('max_capacity', $info->max_capacity ?? 30) }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-5">
                        <label class="form-label">{{ __('app.start_date') }}</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $info?->start_date?->format('Y-m-d') ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">{{ __('app.end_date') }}</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $info?->end_date?->format('Y-m-d') ?? '') }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.zoom_link') }}</label>
                        <input type="url" name="zoom_link" value="{{ old('zoom_link', $info->zoom_link ?? '') }}" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('groups.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
