@php
    $enrolment_programs = \App\Models\Program::active()->get();
    $enrolment_subjects = \App\Models\Subject::active()->get();
    $enrolment_groups = \App\Models\Group::active()->get();
@endphp

<!-- Enrolment Modal -->
<div class="modal fade" id="kt_modal_enrolment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">تشعيب الطلاب</h2>
                <div class="card-toolbar ms-auto me-4">
                    <button type="button" class="btn btn-sm btn-icon btn-light-info" onclick="showModalHelp()" title="شروط ظهور الطلاب">
                        <i class="ki-outline ki-question-2 fs-2"></i>
                    </button>
                </div>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            
            <div class="modal-body py-10 px-lg-17">
                <!-- Filters -->
                <div class="row mb-5">
                    <div class="col-12 col-md">
                        <label class="form-label">البرنامج</label>
                        <select id="modal_filter_program" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_enrolment" data-placeholder="اختر البرنامج">
                            <option></option>
                            @foreach($enrolment_programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label">المادة</label>
                        <select id="modal_filter_subject" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_enrolment" data-placeholder="اختر المادة">
                            <option></option>
                            @foreach($enrolment_subjects as $subject)
                                <option value="{{ $subject->id }}">{{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label">المجموعة الحالية</label>
                        <select id="modal_filter_group" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#kt_modal_enrolment" data-placeholder="اختر المجموعة">
                            <option></option>
                            @foreach($enrolment_groups as $group)
                                <option value="{{ $group->id }}" data-subject-id="{{ $group->subject_id }}">{{ $group->name }} ({{ app()->getLocale() == 'ar' ? ($group->subject->name_ar ?? '') : ($group->subject->name_en ?? '') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label text-primary fw-bold">المجموعة الهدف (للتشعيب)</label>
                        <select id="modal_target_group" class="form-select form-select-solid border-primary" data-control="select2" data-dropdown-parent="#kt_modal_enrolment" data-placeholder="اختر المادة أولاً">
                            <option></option>
                            @foreach($enrolment_groups as $group)
                                <option value="{{ $group->id }}" data-subject-id="{{ $group->subject_id }}">{{ $group->name }} ({{ app()->getLocale() == 'ar' ? ($group->subject->name_ar ?? '') : ($group->subject->name_en ?? '') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md mt-4 mt-md-0 d-flex gap-2 align-items-end">
                        <button type="button" class="btn btn-light-primary w-100" onclick="fetchAllModalStudents()">عرض كل الطلاب</button>
                    </div>
                </div>

                <div class="row">
                    <!-- Right Side: Filtered List -->
                    <div class="col-md-6 border-end pe-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="m-0">الطلاب المطابقين للبحث</h4>
                            <span id="source_count" class="badge badge-light-dark fs-7">0</span>
                        </div>
                        <div id="modal_source_students" class="min-h-350px max-h-500px overflow-auto p-5 border border-dashed border-gray-400 rounded bg-light" style="transition: all 0.3s;" ondrop="dropToSource(event)" ondragover="allowDrop(event)">
                            <p class="text-muted text-center mt-10 empty-msg-source">
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/15.png') }}" alt="اختر فلاتر البحث" class="mw-100 mh-200px theme-light-show" />
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/15-dark.png') }}" alt="اختر فلاتر البحث" class="mw-100 mh-200px theme-dark-show" />
                            </p>
                        </div>
                    </div>

                    <!-- Left Side: Target -->
                    <div class="col-md-6 ps-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="m-0 text-primary">الطلاب المحددين للتشعيب</h4>
                            <span id="target_count" class="badge badge-primary fs-7">0</span>
                        </div>
                        <div id="modal_target_students" class="min-h-350px max-h-500px overflow-auto p-5 border border-dashed border-primary rounded bg-light-primary" style="transition: all 0.3s;" ondrop="dropToTarget(event)" ondragover="allowDrop(event)">
                            <p class="text-muted text-center mt-10 empty-msg">
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/17.png') }}" alt="اسحب الطلاب هنا" class="mw-100 mh-200px theme-light-show" />
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/17-dark.png') }}" alt="اسحب الطلاب هنا" class="mw-100 mh-200px theme-dark-show" />
                            </p>
                        </div>
                        <div class="mt-4 text-center">
                            <button class="btn btn-primary w-100 fs-5 fw-bold py-3" id="btn_modal_enroll" onclick="submitModalEnrolment()">
                                <i class="ki-outline ki-check-circle fs-2 me-2"></i> تنفيذ التشعيب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let modalStudentsData = [];
    let modalTargetSubjectFilter = '';

    // Filters the target-group select2 results to only the groups of the selected subject,
    // without rebuilding the <select> DOM (which breaks Select2's internal option cache).
    function modalTargetGroupMatcher(params, data) {
        if (!data.id) return data; // keep the empty placeholder option always visible

        const subjectId = data.element ? $(data.element).data('subject-id') : null;
        if (modalTargetSubjectFilter && subjectId && subjectId.toString() !== modalTargetSubjectFilter.toString()) {
            return null;
        }

        if ($.trim(params.term || '') !== '' && data.text.toUpperCase().indexOf(params.term.toUpperCase()) === -1) {
            return null;
        }

        return data;
    }

    $(window).on('load', function () {
        const $targetGroup = $('#modal_target_group');
        try { $targetGroup.select2('destroy'); } catch (e) {}
        $targetGroup.select2({
            dropdownParent: $('#kt_modal_enrolment'),
            placeholder: $targetGroup.data('placeholder'),
            matcher: modalTargetGroupMatcher
        });

        $('#modal_filter_subject').on('change', function () {
            modalTargetSubjectFilter = $(this).val();

            // Clear the target group if it no longer belongs to the selected subject
            const currentVal = $targetGroup.val();
            if (currentVal) {
                const optSubjectId = $targetGroup.find(`option[value="${currentVal}"]`).data('subject-id');
                if (modalTargetSubjectFilter && optSubjectId && optSubjectId.toString() !== modalTargetSubjectFilter.toString()) {
                    $targetGroup.val('').trigger('change');
                }
            }
        });

        $('#modal_filter_program, #modal_filter_subject, #modal_filter_group, #modal_target_group').on('change', function () {
            loadModalStudents();
        });
    });

    function showModalHelp() {
        Swal.fire({
            title: 'شروط إظهار الطلاب للتشعيب',
            html: `
                <ul class="fs-5 text-gray-700 text-start" style="direction: rtl;">
                    <li class="mb-2">يجب أن يكون حساب الطالب <strong>فعالاً</strong>.</li>
                    <li class="mb-2">يجب أن يكون الطالب قد <strong>فعل بريده الإلكتروني</strong>.</li>
                    <li class="mb-2">يجب ألا يكون على الطالب <strong>أي رسوم قيد المراجعة أو غير مؤكدة</strong>.</li>
                    <li class="mb-2">يجب أن يكون الطالب قد سدد <strong>الحد الأدنى</strong> المطلوب من الدفعات لجميع مواده الحالية.</li>
                </ul>
            `,
            icon: 'info',
            confirmButtonText: 'حسناً'
        });
    }

    function fetchAllModalStudents() {
        modalTargetSubjectFilter = '';
        $('#modal_filter_program').val('').trigger('change.select2');
        $('#modal_filter_subject').val('').trigger('change.select2');
        $('#modal_filter_group').val('').trigger('change.select2');
        loadModalStudents();
    }

    function loadModalStudents() {
        const programId = $('#modal_filter_program').val();
        const subjectId = $('#modal_filter_subject').val();
        const groupId = $('#modal_filter_group').val();
        const targetGroupId = $('#modal_target_group').val();

        $.ajax({
            url: "{{ route('enrolments.students') }}",
            type: 'GET',
            data: {
                program_id: programId,
                subject_id: subjectId,
                group_id: groupId,
                target_group_id: targetGroupId
            },
            success: function(response) {
                modalStudentsData = response;
                renderModalSourceStudents();
            }
        });
    }

    function renderModalSourceStudents() {
        const sourceContainer = $('#modal_source_students');
        sourceContainer.empty();
        
        const targetIds = getModalTargetStudentIds();

        if (modalStudentsData.length === 0) {
            sourceContainer.html('<p class="text-muted text-center mt-10">لا يوجد طلاب مطابقين</p>');
            updateStudentCounts();
            return;
        }

        modalStudentsData.forEach(student => {
            if (targetIds.includes(student.id.toString())) return;

            let conflictClass = student.has_conflict ? 'border-danger bg-light-danger text-danger' : 'border-success bg-light-success';
            let conflictMsg = student.has_conflict ? '<span class="badge badge-danger ms-2">تعارض</span>' : '';
            
            // Generate Subject Badges
            let subjectsHtml = '';
            if (student.registrations && student.registrations.length > 0) {
                student.registrations.forEach(reg => {
                    if (reg.subject) {
                        let subjectName = reg.subject.name_ar || reg.subject.name_en || '';
                        subjectsHtml += `<span class="badge badge-light-primary me-1 mb-1">${subjectName}</span>`;
                    }
                });
            } else {
                subjectsHtml = '<span class="text-muted fs-8">غير مسجل بأي مادة</span>';
            }

            let imageSrc = '{{ asset("assets/admin/media/avatars/blank.png") }}';
            if (student.image) {
                if (student.image.startsWith('site/')) {
                    imageSrc = '/' + student.image;
                } else {
                    imageSrc = '/storage/' + student.image;
                }
            }

            const el = `
                <div id="modal_student_${student.id}" class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded cursor-pointer student-item ${conflictClass}" draggable="true" ondragstart="dragModal(event)">
                    <div class="d-flex align-items-center flex-grow-1" onclick="moveToModalTarget(${student.id})">
                        <div class="symbol symbol-40px me-3">
                            <img src="${imageSrc}" alt="" />
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bolder text-gray-800 text-hover-primary">${student.full_name_ar}</span>
                            <span class="text-muted fw-bold d-block fs-7">${student.phone ?? ''}</span>
                            <div class="mt-1">${subjectsHtml}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center ms-2">
                        ${conflictMsg}
                        <button type="button" class="btn btn-icon btn-sm btn-light-info ms-2" onclick="showModalStudentDetails(${student.id}); event.stopPropagation();" title="التفاصيل">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </button>
                    </div>
                </div>
            `;
            sourceContainer.append(el);
        });
        
        updateStudentCounts();
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function dragModal(ev) {
        ev.dataTransfer.setData("text", ev.currentTarget.id);
    }

    function dropToTarget(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        if(data.startsWith('modal_student_')) {
            moveToModalTarget(data.replace('modal_student_', ''));
        }
    }

    function dropToSource(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        if(data.startsWith('modal_student_')) {
            moveToModalSource(data.replace('modal_student_', ''));
        }
    }

    function moveToModalTarget(id) {
        const el = $(`#modal_student_${id}`);
        if(el.length) {
            const student = modalStudentsData.find(s => s.id == id);
            if (student && student.has_conflict) {
                toastr.warning('تنبيه: هذا الطالب لديه تعارض في المواعيد مع المجموعة الهدف!');
            }

            el.find('.flex-grow-1').first().attr('onclick', `moveToModalSource(${id})`);
            
            // Add exclude button if not exists
            if (el.find('.exclude-btn').length === 0) {
                el.find('.d-flex.align-items-center.ms-2').prepend(`
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger ms-2 exclude-btn" onclick="moveToModalSource(${id}); event.stopPropagation();" title="استثناء">
                        <i class="ki-outline ki-cross fs-4"></i>
                    </button>
                `);
            }

            $('#modal_target_students').append(el);
            $('#modal_target_students .empty-msg').hide();
            updateStudentCounts();
        }
    }

    function moveToModalSource(id) {
        const el = $(`#modal_student_${id}`);
        if(el.length) {
            el.find('.flex-grow-1').first().attr('onclick', `moveToModalTarget(${id})`);
            el.find('.exclude-btn').remove();

            $('#modal_source_students').append(el);
            if($('#modal_target_students .student-item').length === 0) {
                $('#modal_target_students .empty-msg').show();
            }
            if($('#modal_source_students .empty-msg-source').length) {
                $('#modal_source_students .empty-msg-source').hide();
            }
            updateStudentCounts();
        }
    }

    function updateStudentCounts() {
        $('#source_count').text($('#modal_source_students .student-item').length);
        $('#target_count').text($('#modal_target_students .student-item').length);
    }

    function showModalStudentDetails(id) {
        const student = modalStudentsData.find(s => s.id == id);
        if (!student) return;

        let detailsHtml = `
            <table class="table table-row-dashed table-row-gray-300 gy-4 text-start" style="direction: rtl;">
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
        Swal.fire({
            title: 'تفاصيل الطالب',
            html: detailsHtml,
            width: 600,
            confirmButtonText: 'إغلاق'
        });
    }

    function getModalTargetStudentIds() {
        let ids = [];
        $('#modal_target_students .student-item').each(function() {
            ids.push($(this).attr('id').replace('modal_student_', ''));
        });
        return ids;
    }

    function submitModalEnrolment() {
        const ids = getModalTargetStudentIds();
        const targetGroupId = $('#modal_target_group').val();

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
                    url: "{{ route('enrolments.enroll') }}",
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
                                    moveToModalSource(f.id);
                                });
                            } else {
                                $('#modal_target_students .student-item').remove();
                                $('#modal_target_students .empty-msg').show();
                            }
                            loadModalStudents();
                            
                            // Close modal and refresh page if needed
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
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
