{{--
    Reusable "pick a file from the File Manager" field.
    Usage: @include('admin.components.file-picker', ['name' => 'image', 'value' => $info->image ?? null, 'label' => 'الصورة', 'folder' => 'programs'])
    Renders a hidden text input named $name holding the storage-relative path
    (e.g. "programs/xyz.jpg") — controllers should save this value directly,
    no $request->file()/->store() needed since the file already lives in storage.
--}}
@php
    $fmFieldId = ($name ?? 'file') . '_field';
    $fmValue = $value ?? null;
    $fmCurrentUrl = $fmValue ? (Illuminate\Support\Str::startsWith($fmValue, ['http', 'site/']) ? asset($fmValue) : asset('storage/' . $fmValue)) : null;
    $fmIsImage = $fmValue && in_array(strtolower(pathinfo($fmValue, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp']);
@endphp
<div class="fm-picker-field">
    @if($label ?? null)
        <label class="p-2 fw-semibold fs-6 d-block">{{ $label }}</label>
    @endif
    <div class="d-flex align-items-center gap-4">
        <div class="symbol symbol-75px bg-light-primary rounded d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width:75px;height:75px;">
            <img id="{{ $fmFieldId }}_preview_img" src="{{ $fmCurrentUrl }}" class="{{ $fmIsImage ? '' : 'd-none' }}" style="width:75px;height:75px;object-fit:cover;" alt="">
            <i id="{{ $fmFieldId }}_preview_icon" class="ki-duotone ki-file fs-2x text-primary {{ $fmIsImage ? 'd-none' : '' }}"><span class="path1"></span><span class="path2"></span></i>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <button type="button" class="btn btn-light-primary btn-sm"
                        onclick="openFileManagerPicker('{{ $fmFieldId }}'{{ isset($folder) ? ", { folder: '".$folder."' }" : '' }})">
                    <i class="ki-duotone ki-folder fs-4"><span class="path1"></span><span class="path2"></span></i>
                    @lang('app.browse_file_manager')
                </button>
                <a id="{{ $fmFieldId }}_view_link" href="{{ $fmCurrentUrl }}" target="_blank" class="btn btn-light btn-sm {{ $fmCurrentUrl ? '' : 'd-none' }}">
                    @lang('app.view')
                </a>
            </div>
            <span id="{{ $fmFieldId }}_preview_name" class="text-muted fs-8 d-block">{{ $fmValue ? basename($fmValue) : __('app.no_file_selected') }}</span>
        </div>
    </div>
    <input type="text" name="{{ $name }}" id="{{ $fmFieldId }}" value="{{ $fmValue }}" class="d-none">
</div>
