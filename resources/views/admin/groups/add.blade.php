@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.groups') - {{ isset($subject) ? (app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en) : 'إضافة مجموعة' }}
@stop
@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('programs.view') }}" class="text-muted text-hover-primary">@lang('app.programs')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    @if(isset($program))
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('programs.subjects.view', Crypt::encrypt($program->id)) }}" class="text-muted text-hover-primary">@lang('app.subjects')</a>
        </li>
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-400 w-5px h-2px"></span>
        </li>
    @endif
    @if(isset($subject))
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('subjects.groups.view', Crypt::encrypt($subject->id)) }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
        </li>
    @else
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('groups.view') }}" class="text-muted text-hover-primary">@lang('app.groups')</a>
        </li>
    @endif
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.' . ($info && $info->id ? 'edit' : 'add'))</li>
@endsection
@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')
                        <form
                            action="{{ $info && $info->id ? route('groups.edit.submit', [Crypt::encrypt($subject->id), Crypt::encrypt($info->id)]) : (isset($subject) ? route('groups.add.submit', Crypt::encrypt($subject->id)) : route('groups.add.global.submit')) }}"
                            method="POST">
                            @csrf
                            @if(isset($subject))
                                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                            @else
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="p-2 required">المادة الدراسية (Subject)</label>
                                        <select name="subject_id" class="form-select" data-control="select2" data-placeholder="اختر المادة الدراسية" required>
                                            <option value=""></option>
                                            @foreach ($subjects as $sub)
                                                <option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>
                                                    {{ app()->getLocale() == 'ar' ? $sub->name_ar : $sub->name_en }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">الاسم</label>
                                            <input type="text" name="name" value="{{ old('name', $info->name ?? '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            @include('admin.components.file-picker', ['name' => 'image', 'value' => $info->image ?? old('image'), 'label' => 'صورة المجموعة', 'folder' => 'groups'])
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2 required">المعلم</label>
                                            <select name="teacher_id" class="form-select" data-control="select2" required>
                                                <option value="">اختر المعلم</option>
                                                @php $selectedTeacher = old('teacher_id', $info->teacher_id ?? ''); @endphp
                                                @foreach ($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ $selectedTeacher == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="p-2 required">أيام الدراسة</label>
                                            @php $selectedDays = old('days', $info->days ?? []); @endphp
                                            <div class="d-flex flex-wrap gap-5 mt-2">
                                                @foreach(['sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة'] as $val => $label)
                                                    <label class="form-check form-check-custom form-check-solid">
                                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $val }}" {{ in_array($val, is_array($selectedDays) ? $selectedDays : []) ? 'checked' : '' }}/>
                                                        <span class="form-check-label fw-semibold text-gray-700">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="p-2 required">وقت البدء</label>
                                            <input type="time" name="start_time" value="{{ old('start_time', isset($info->start_time) ? \Carbon\Carbon::parse($info->start_time)->format('H:i') : '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">وقت الانتهاء</label>
                                            <input type="time" name="end_time" value="{{ old('end_time', isset($info->end_time) ? \Carbon\Carbon::parse($info->end_time)->format('H:i') : '') }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="p-2 required">السعة القصوى (الطلاب)</label>
                                            <input type="number" name="max_capacity" value="{{ old('max_capacity', $info->max_capacity ?? '') }}"
                                                class="form-control" min="1" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">تاريخ البدء</label>
                                            <input type="date" name="start_date" value="{{ old('start_date', isset($info->start_date) ? \Carbon\Carbon::parse($info->start_date)->format('Y-m-d') : '') }}" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="p-2">تاريخ الانتهاء</label>
                                            <input type="date" name="end_date" value="{{ old('end_date', isset($info->end_date) ? \Carbon\Carbon::parse($info->end_date)->format('Y-m-d') : '') }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="p-2">رابط Zoom</label>
                                            <input type="url" name="zoom_link" value="{{ old('zoom_link', $info->zoom_link ?? '') }}" class="form-control">
                                        </div>
                                        <div class="col-md-6 d-flex flex-column justify-content-center align-items-center">
                                            <label class="p-2 fw-semibold fs-6">@lang('app.status')</label>
                                            @php $statusValue = old('is_active', $info->is_active ?? 1); @endphp
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                    {{ $statusValue == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center pt-2">
                                <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                                <a href="{{ isset($subject) ? route('subjects.groups.view', Crypt::encrypt($subject->id)) : route('groups.view') }}" class="btn btn-light me-3">@lang('app.cancel')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
