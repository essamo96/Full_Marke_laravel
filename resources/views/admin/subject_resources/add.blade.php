@extends('layouts.admin')

@section('title', isset($info) ? __('app.edit') : __('app.add_new'))

@php($pageTitle = isset($info) ? __('app.edit') : __('app.add_new'))

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST"
                  action="{{ isset($info) ? route('subject_resources.edit.submit', [$subject, \Illuminate\Support\Facades\Crypt::encrypt($info->id)]) : route('subject_resources.add.submit', $subject) }}">
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
                        <label class="form-label required">{{ __('app.name') }}</label>
                        <input type="text" name="title" value="{{ old('title', $info->title ?? '') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Type</label>
                        <select name="type" class="form-select" required>
                            @foreach (['video', 'document', 'link', 'zoom'] as $type)
                                <option value="{{ $type }}" @selected(old('type', $info->type ?? '') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label required">URL</label>
                    <input type="text" name="url" value="{{ old('url', $info->url ?? '') }}" class="form-control" required>
                </div>

                <div class="mb-5">
                    <label class="form-label">{{ __('app.details') }}</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $info->description ?? '') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-5">
                        <label class="form-label">{{ __('app.sort') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $info->sort_order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-5">
                        <label class="form-label d-block">{{ __('app.status') }}</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $info->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('app.active') }}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                <a href="{{ route('subject_resources.view', $subject) }}" class="btn btn-light">{{ __('app.cancel') }}</a>
            </form>
        </div>
    </div>
@endsection
