<div class="d-flex justify-content-start gap-2 flex-shrink-0">
@can('admin.teachers.edit')
<a href="{{ route('teachers.edit', Crypt::encrypt($teacher->id)) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.teachers.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($teacher->id) }}" data-name="{{ $teacher->name ?? '' }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan
@can('admin.teachers.change_password')
<a class="btn btn-icon btn-warning btn-sm" href="javascript:void(0)" data-id="{{ Crypt::encrypt($teacher->id) }}" data-name="{{ $teacher->name ?? '' }}" data-bs-toggle="modal" data-bs-target="#changePasswordModal" title="تغيير كلمة المرور">
    <i class="bi bi-key-fill fs-5"></i>
</a>
@endcan

</div>