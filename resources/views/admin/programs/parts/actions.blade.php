<div class="d-flex justify-content-start gap-2 flex-shrink-0">
@can('admin.programs.edit')
<a href="{{ route('programs.edit', Crypt::encrypt($program->id)) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.programs.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($program->id) }}" data-name="{{ app()->getLocale() == 'ar' ? $program->name_ar : $program->name_en }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan

</div>