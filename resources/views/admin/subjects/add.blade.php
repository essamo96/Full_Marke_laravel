@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('subjects.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('subjects.add.submit') }}"
                  enctype="multipart/form-data">
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
                        <label class="form-label required">{{ __('app.program') }}</label>
                        <select name="program_id" class="form-select" required>
                            <option value="">-- {{ __('app.program') }} --</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}" @selected(old('program_id', $info->program_id ?? '') == $program->id)>{{ $program->title_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.name') }} (AR)</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $info->name_ar ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.name') }} (EN)</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $info->name_en ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.details') }} (AR)</label>
                        <textarea name="description_ar" class="form-control" rows="2">{{ old('description_ar', $info->description_ar ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.details') }} (EN)</label>
                        <textarea name="description_en" class="form-control" rows="2">{{ old('description_en', $info->description_en ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">{{ __('app.fee') }}</label>
                        <input type="number" step="0.01" min="0" name="fee" value="{{ old('fee', $info->fee ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label required">{{ __('app.min_payment') }}</label>
                        <input type="number" step="0.01" min="0" name="min_payment" value="{{ old('min_payment', $info->min_payment ?? '') }}" class="form-control" required>
                        <div class="form-text">{{ __('app.min_payment_error') }}</div>
                    </div>
                    <div class="col-md-4 mb-5">
                        <label class="form-label">{{ __('app.image') }}</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-5">
                        <label class="form-label">{{ __('app.assigned_teachers') }}</label>
                        <select name="teacher_ids[]" class="form-select" multiple size="5">
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                        @selected(isset($info) && $info->teachers->contains('id', $teacher->id))>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-5">
                        <label class="form-label">{{ __('app.sort') }}</label>
                        <input type="number" name="order" value="{{ old('order', $info->order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-2 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('subjects.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
