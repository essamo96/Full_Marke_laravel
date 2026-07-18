<div class="d-flex justify-content-start gap-2 flex-shrink-0">
@can('admin.programs.edit')
<a href="{{ route('groups.edit', [Crypt::encrypt($subject ? $subject->id : $group->subject_id), Crypt::encrypt($group->id)]) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.programs.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($group->id) }}" data-name="{{ $group->name }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan
@can('admin.groups.generate_code')
<a href="javascript:void(0)" class="btn btn-icon btn-info btn-sm btn-generate-code" data-id="{{ Crypt::encrypt($group->id) }}" data-bs-toggle="tooltip" title="إنشاء كود الانضمام">
    <i class="bi bi-qr-code fs-5"></i>
</a>
@endcan
</div>