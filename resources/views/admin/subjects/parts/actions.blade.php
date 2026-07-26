<div class="d-flex justify-content-start gap-2 flex-shrink-0">
@can('admin.programs.edit')
<a href="{{ route('subjects.edit', [Crypt::encrypt($program ? $program->id : $subject->program_id), Crypt::encrypt($subject->id)]) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
<button type="button" class="btn btn-icon btn-info btn-sm btn-show-qr" data-url="{{ route('qr.subject', $subject->id) }}" data-name="{{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}" title="QR Code">
    <i class="bi bi-qr-code fs-5"></i>
</button>
@can('admin.programs.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($subject->id) }}" data-name="{{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan

</div>