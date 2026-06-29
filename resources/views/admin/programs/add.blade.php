@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('programs.edit.submit', \Illuminate\Support\Facades\Crypt::encrypt($info->id)) : route('programs.add.submit') }}"
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
                        <label class="form-label required">{{ __('app.program_name_ar') }}</label>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $info->title_ar ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.program_name_en') }}</label>
                        <input type="text" name="title_en" value="{{ old('title_en', $info->title_en ?? '') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.route_name') }} (slug)</label>
                        <input type="text" name="slug" value="{{ old('slug', $info->slug ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">{{ __('app.program_type') }}</label>
                        <select name="type" class="form-select" required>
                            @foreach (['regular', 'children', 'rehabilitation'] as $type)
                                <option value="{{ $type }}" @selected(old('type', $info->type ?? 'regular') === $type)>{{ __('app.type_'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.short_description') }} (AR)</label>
                        <textarea name="description_ar" class="form-control" rows="3">{{ old('description_ar', $info->description_ar ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.short_description') }} (EN)</label>
                        <textarea name="description_en" class="form-control" rows="3">{{ old('description_en', $info->description_en ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.image') }}</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if (! empty($info?->image))
                            <img src="{{ asset('storage/'.$info->image) }}" class="mt-2 rounded" width="80">
                        @endif
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label">{{ __('app.sort') }}</label>
                        <input type="number" name="order" value="{{ old('order', $info->order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-3 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('programs.view') }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
