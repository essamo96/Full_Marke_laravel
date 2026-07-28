@can('admin.teachers.status')
<div class="form-check form-switch mt-2">
    <input class="form-check-input status" type="checkbox" data-href="{{ Crypt::encrypt($teacher->id) }}" value="1"
        {{ $teacher->status == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
</div>
@endcan
