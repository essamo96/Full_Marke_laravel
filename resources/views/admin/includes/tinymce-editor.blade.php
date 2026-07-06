@php
    // Generate unique ID for each editor
    $editorId = $id ?? 'tinymce_' . str_replace(['[', ']'], '_', $name) . '_' . uniqid();
@endphp

<div class="tinymce-editor-wrapper {{ $class ?? '' }}">
    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ ($required ?? false) ? 'required' : '' }}
        class="form-control form-control-solid"
        style="min-height: {{ $height ?? 400 }}px;"
        data-height="{{ $height ?? 400 }}"
    >{{ $value ?? '' }}</textarea>
</div>
