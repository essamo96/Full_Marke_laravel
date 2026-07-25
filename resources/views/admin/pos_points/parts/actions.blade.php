<div class="d-flex justify-content-start gap-2 flex-shrink-0">
@can('admin.pos_points.edit')
<a href="{{ route('pos_points.edit', Crypt::encrypt($point->id)) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.pos_points.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($point->id) }}" data-name="{{ app()->getLocale() == 'ar' ? $point->name_ar : $point->name_en }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan
</div>
