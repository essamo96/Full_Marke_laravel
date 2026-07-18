<!-- Student Inquiry Offcanvas -->
<div id="kt_student_inquiry" class="bg-white" data-kt-drawer="true" data-kt-drawer-name="student_inquiry" data-kt-drawer-activate="true" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_student_inquiry_toggle" data-kt-drawer-close="#kt_student_inquiry_close">
    <div class="card w-100 rounded-0 border-0">
        <div class="card-header pe-5">
            <div class="card-title">
                <div class="d-flex justify-content-center flex-column me-3">
                    <a href="#" class="fs-4 fw-bolder text-gray-900 text-hover-primary me-1 lh-1">استعلامات الطلاب</a>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="btn btn-sm btn-icon btn-active-light-primary" id="kt_student_inquiry_close">
                    <i class="ki-duotone ki-cross fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
        </div>
        <div class="card-body hover-scroll-overlay-y">
            <!-- Search Form -->
            <div class="mb-5">
                <label class="form-label">بحث باسم، رقم جوال، او هوية</label>
                <input type="text" id="inquiry_keyword" class="form-control form-control-solid" placeholder="ادخل كلمة البحث...">
            </div>
            <div class="row mb-5">
                <div class="col-6">
                    <label class="form-label">البرنامج</label>
                    <select id="inquiry_program" class="form-select form-select-solid" data-control="select2">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Program::active()->get() as $p)
                            <option value="{{$p->id}}">{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">المادة</label>
                    <select id="inquiry_subject" class="form-select form-select-solid" data-control="select2">
                        <option value="">الكل</option>
                        @foreach(\App\Models\Subject::active()->get() as $s)
                            <option value="{{$s->id}}">{{$s->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-5">
                <label class="form-label">المجموعة</label>
                <select id="inquiry_group" class="form-select form-select-solid" data-control="select2">
                    <option value="">الكل</option>
                    @foreach(\App\Models\Group::active()->get() as $g)
                        <option value="{{$g->id}}">{{$g->name}}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-primary w-100 mb-5" onclick="performStudentInquiry()">بحث</button>

            <!-- Results -->
            <div id="inquiry_results" class="mt-5">
                <!-- Data will be appended here -->
            </div>
        </div>
    </div>
</div>

<script>
    function performStudentInquiry() {
        const keyword = $('#inquiry_keyword').val();
        const program_id = $('#inquiry_program').val();
        const subject_id = $('#inquiry_subject').val();
        const group_id = $('#inquiry_group').val();
        const resultsContainer = $('#inquiry_results');

        resultsContainer.html('<div class="text-center mt-5"><div class="spinner-border text-primary" role="status"></div></div>');

        $.ajax({
            url: "{{ route('student_inquiry.search') }}",
            type: 'GET',
            data: { keyword, program_id, subject_id, group_id },
            success: function(response) {
                resultsContainer.empty();
                if(response.length === 0) {
                    resultsContainer.html('<p class="text-center text-muted mt-5">لا يوجد نتائج</p>');
                    return;
                }
                
                response.forEach(student => {
                    resultsContainer.append(`
                        <div class="d-flex align-items-center p-3 mb-3 border border-dashed rounded bg-light">
                            <div class="symbol symbol-40px me-3">
                                <img src="${student.image ? '/' + student.image : '/assets/media/avatars/blank.png'}" />
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <a href="/admin/students/${student.id}" class="fw-bolder text-gray-800 text-hover-primary">${student.full_name_ar}</a>
                                <span class="text-muted fw-bold d-block fs-7">${student.phone ?? 'بدون رقم'} | ${student.national_id ?? 'بدون هوية'}</span>
                            </div>
                            <div>
                                <a href="/admin/students/${student.id}/edit" class="btn btn-icon btn-light-primary btn-sm me-1" title="تعديل"><i class="ki-duotone ki-pencil fs-3"><span class="path1"></span><span class="path2"></span></i></a>
                            </div>
                        </div>
                    `);
                });
            },
            error: function() {
                resultsContainer.html('<p class="text-danger mt-5">حدث خطأ أثناء البحث</p>');
            }
        });
    }
</script>
