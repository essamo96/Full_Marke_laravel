@can('admin.programs.edit')
<a href="{{ route('groups.edit', [Crypt::encrypt($subject ? $subject->id : $group->subject_id), Crypt::encrypt($group->id)]) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.programs.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($group->id) }}" data-name="{{ $group->name }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan
