@extends('admin.layout.main_master')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">تشعيب ونقل الطلاب</h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" data-bs-toggle="modal" data-bs-target="#helpModal" title="شروط ظهور الطلاب">
                <i class="ki-outline ki-question-2 fs-2"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-5 align-items-end">
            <div class="col-md-3">
                <label>البرنامج</label>
                <select id="filter_program" class="form-select form-select-solid" data-control="select2" data-placeholder="اختر البرنامج">
                    <option></option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>المادة</label>
                <select id="filter_subject" class="form-select form-select-solid" data-control="select2" data-placeholder="اختر المادة">
                    <option></option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>المجموعة الحالية</label>
                <select id="filter_group" class="form-select form-select-solid" data-control="select2" data-placeholder="اختر المجموعة">
                    <option></option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->subject->name ?? '' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>المجموعة الهدف (للتشعيب/النقل)</label>
                <select id="target_group" class="form-select form-select-solid border-primary" data-control="select2" data-placeholder="اختر المجموعة الهدف">
                    <option></option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->subject->name ?? '' }}) - {{ $group->start_time }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mt-4 mt-md-0 d-flex gap-2 align-items-end">
                <button type="button" class="btn btn-light-primary w-100" onclick="fetchAllStudents()">كل الطلاب</button>
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Target -->
            <div class="col-md-6 border-end pe-5">
                <h4>الطلاب المحددين للتشعيب</h4>
                <div id="target_students" class="min-h-300px p-5 border border-dashed border-primary rounded bg-light-primary" ondrop="drop(event)" ondragover="allowDrop(event)">
                    <p class="text-muted text-center mt-10 empty-msg">اسحب الطلاب هنا أو اضغط عليهم في القائمة اليمنى</p>
                </div>
                <div class="mt-4 text-center">
                    <button class="btn btn-primary w-100" id="btn_enroll" onclick="enrollStudents()">تنفيذ التشعيب/النقل</button>
                </div>
            </div>

            <!-- Right Side: Filtered List -->
            <div class="col-md-6 ps-5">
                <h4>الطلاب المطابقين للبحث</h4>
                <div id="source_students" class="min-h-300px p-5 border border-dashed rounded bg-light" ondrop="dropToSource(event)" ondragover="allowDrop(event)">
                    <!-- Students will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">شروط إظهار الطلاب للتشعيب</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body">
                <ul class="fs-5 text-gray-700">
                    <li class="mb-2">يجب أن يكون حساب الطالب <strong>فعالاً</strong>.</li>
                    <li class="mb-2">يجب أن يكون الطالب قد <strong>فعل بريده الإلكتروني</strong>.</li>
                    <li class="mb-2">يجب ألا يكون على الطالب <strong>أي رسوم قيد المراجعة أو غير مؤكدة</strong>.</li>
                    <li class="mb-2">يجب أن يكون الطالب قد سدد <strong>الحد الأدنى</strong> المطلوب من الدفعات لجميع مواده الحالية.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تفاصيل الطالب</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let studentsData = [];

    $('#filter_program, #filter_subject, #filter_group, #target_group').on('change', function() {
        loadStudents();
    });

    function fetchAllStudents() {
        $('#filter_program').val(null).trigger('change.select2');
        $('#filter_subject').val(null).trigger('change.select2');
        $('#filter_group').val(null).trigger('change.select2');
        loadStudents();
    }

    function loadStudents() {
        const programId = $('#filter_program').val();
        const subjectId = $('#filter_subject').val();
        const groupId = $('#filter_group').val();
        const targetGroupId = $('#target_group').val();

        $.ajax({
            url: "{{ route('admin.enrolments.students') }}",
            type: 'GET',
            data: {
                program_id: programId,
                subject_id: subjectId,
                group_id: groupId,
                target_group_id: targetGroupId
            },
            success: function(response) {
                studentsData = response;
                renderSourceStudents();
            }
        });
    }

    function renderSourceStudents() {
        const sourceContainer = $('#source_students');
        sourceContainer.empty();
        
        const targetIds = getTargetStudentIds();

        studentsData.forEach(student => {
            if (targetIds.includes(student.id.toString())) return;

            let conflictClass = student.has_conflict ? 'border-danger bg-light-danger text-danger' : 'border-success bg-light-success';
            let conflictMsg = student.has_conflict ? '<span class="badge badge-danger ms-2">تعارض</span>' : '';
            
            // Generate Subject Badges
            let subjectsHtml = '';
            if (student.registrations && student.registrations.length > 0) {
                student.registrations.forEach(reg => {
                    if (reg.subject) {
                        subjectsHtml += `<span class="badge badge-light-primary me-1 mb-1">${reg.subject.name}</span>`;
                    }
                });
            } else {
                subjectsHtml = '<span class="text-muted fs-8">غير مسجل بأي مادة</span>';
            }

            const el = `
                <div id="student_${student.id}" class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded cursor-pointer student-item ${conflictClass}" draggable="true" ondragstart="drag(event)">
                    <div class="d-flex align-items-center flex-grow-1" onclick="moveToTarget(${student.id})">
                        <div class="symbol symbol-40px me-3">
                            <img src="${student.image ? '/' + student.image : '/assets/media/avatars/blank.png'}" alt="" />
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bolder text-gray-800 text-hover-primary">${student.full_name_ar}</span>
                            <span class="text-muted fw-bold d-block fs-7">${student.phone ?? ''}</span>
                            <div class="mt-1">${subjectsHtml}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center ms-2">
                        ${conflictMsg}
                        <button type="button" class="btn btn-icon btn-sm btn-light-info ms-2" onclick="showStudentDetails(${student.id}); event.stopPropagation();" title="التفاصيل">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </button>
                    </div>
                </div>
            `;
            sourceContainer.append(el);
        });
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        moveToTarget(data.replace('student_', ''));
    }

    function dropToSource(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        moveToSource(data.replace('student_', ''));
    }

    function moveToTarget(id) {
        const el = $(`#student_${id}`);
        if(el.length) {
            const student = studentsData.find(s => s.id == id);
            if (student && student.has_conflict) {
                toastr.warning('تنبيه: هذا الطالب لديه تعارض في المواعيد مع المجموعة الهدف!');
            }

            // Change click action to moveToSource
            el.find('.flex-grow-1').first().attr('onclick', `moveToSource(${id})`);
            
            // Add exclude button if not exists
            if (el.find('.exclude-btn').length === 0) {
                el.find('.d-flex.align-items-center.ms-2').prepend(`
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger ms-2 exclude-btn" onclick="moveToSource(${id}); event.stopPropagation();" title="استثناء">
                        <i class="ki-outline ki-cross fs-4"></i>
                    </button>
                `);
            }

            $('#target_students').append(el);
            $('#target_students .empty-msg').hide();
        }
    }

    function moveToSource(id) {
        const el = $(`#student_${id}`);
        if(el.length) {
            el.find('.flex-grow-1').first().attr('onclick', `moveToTarget(${id})`);
            el.find('.exclude-btn').remove();
            
            $('#source_students').append(el);
            if($('#target_students .student-item').length === 0) {
                $('#target_students .empty-msg').show();
            }
        }
    }

    function showStudentDetails(id) {
        const student = studentsData.find(s => s.id == id);
        if (!student) return;

        let detailsHtml = `
            <table class="table table-row-dashed table-row-gray-300 gy-4">
                <tbody>
                    <tr><td class="fw-bold">الاسم (عربي)</td><td>${student.full_name_ar ?? '-'}</td></tr>
                    <tr><td class="fw-bold">الاسم (إنجليزي)</td><td>${student.full_name_en ?? '-'}</td></tr>
                    <tr><td class="fw-bold">رقم الهوية</td><td>${student.national_id ?? '-'}</td></tr>
                    <tr><td class="fw-bold">رقم الجوال</td><td dir="ltr">${student.phone ?? '-'}</td></tr>
                    <tr><td class="fw-bold">البريد الإلكتروني</td><td>${student.email ?? '-'}</td></tr>
                    <tr><td class="fw-bold">العنوان</td><td>${student.address ?? '-'}</td></tr>
                </tbody>
            </table>
        `;
        $('#studentDetailsContent').html(detailsHtml);
        $('#studentDetailsModal').modal('show');
    }

    function getTargetStudentIds() {
        let ids = [];
        $('#target_students .student-item').each(function() {
            ids.push($(this).attr('id').replace('student_', ''));
        });
        return ids;
    }

    function enrollStudents() {
        const ids = getTargetStudentIds();
        const targetGroupId = $('#target_group').val();

        if (ids.length === 0) {
            toastr.error('يرجى تحديد طلاب للتشعيب');
            return;
        }

        if (!targetGroupId) {
            toastr.error('يرجى تحديد المجموعة الهدف');
            return;
        }

        Swal.fire({
            title: 'تأكيد التشعيب',
            text: 'سيتم إتمام عملية التشعيب/النقل، وفي حال اختيار مجموعة تابعة لمادة غير مسجل فيها الطالب، سيتم إنشاء قيد مالي وإضافته تلقائياً. هل أنت متأكد؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، قم بالتشعيب!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.enrolments.enroll') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_ids: ids,
                        target_group_id: targetGroupId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم بنجاح!', response.message, 'success');
                            if (response.failed && response.failed.length > 0) {
                                response.failed.forEach(f => {
                                    toastr.error(`فشل تشعيب ${f.id}: ${f.reason}`);
                                    moveToSource(f.id);
                                });
                            } else {
                                $('#target_students .student-item').remove();
                                $('#target_students .empty-msg').show();
                            }
                            loadStudents();
                        }
                    },
                    error: function(err) {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء التشعيب', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
