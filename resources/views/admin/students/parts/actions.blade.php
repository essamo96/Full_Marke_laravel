<div class="d-flex justify-content-start gap-2 flex-shrink-0">
<a href="{{ route('students.show', Crypt::encrypt($student->id)) }}" class="btn btn-icon btn-info btn-sm">
    <i class="bi bi-eye fs-5"></i>
</a>
<a href="javascript:void(0)" onclick="loadInvoices('{{ Crypt::encrypt($student->id) }}')" class="btn btn-icon btn-success btn-sm" title="الفواتير">
    <i class="bi bi-receipt fs-5"></i>
</a>
@can('admin.students.results')
<a href="{{ route('students.results', Crypt::encrypt($student->id)) }}" class="btn btn-icon btn-warning btn-sm" title="النتائج">
    <i class="ki-duotone ki-chart-simple fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
</a>
@endcan
@can('admin.students.edit')
<a href="{{ route('students.edit', Crypt::encrypt($student->id)) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i></a>
@endcan
@can('admin.students.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($student->id) }}" data-name="{{ app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan

</div>