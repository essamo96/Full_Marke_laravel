@php
    $enrolment_transfer_subjects = \App\Models\Subject::active()->get();
@endphp

<!-- Transfer To New Subject Modal -->
<div class="modal fade" id="kt_modal_transfer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">نقل طالب / تسجيل في مادة جديدة</h2>
                <div class="card-toolbar ms-auto me-4">
                    <button type="button" class="btn btn-sm btn-icon btn-light-info" onclick="showTransferModalHelp()" title="آلية النقل">
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
                        <label class="required form-label text-primary fw-bold">المادة الهدف (الجديدة)</label>
                        <select id="transfer_target_subject" class="form-select form-select-solid border-primary" data-control="select2" data-dropdown-parent="#kt_modal_transfer" data-placeholder="اختر المادة الجديدة">
                            <option></option>
                            @foreach($enrolment_transfer_subjects as $subject)
                                <option value="{{ $subject->id }}">{{ app()->getLocale() == 'ar' ? $subject->name_ar : $subject->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md mt-4 mt-md-0 d-flex gap-2 align-items-end">
                        <button type="button" class="btn btn-light-primary w-100" onclick="fetchAllTransferStudents()">عرض كل الطلاب</button>
                    </div>
                </div>

                <div class="row">
                    <!-- Right Side: All students -->
                    <div class="col-md-6 border-end pe-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="m-0">الطلاب</h4>
                            <span id="transfer_source_count" class="badge badge-light-dark fs-7">0</span>
                        </div>
                        <div id="transfer_source_students" class="min-h-350px max-h-500px overflow-auto p-5 border border-dashed border-gray-400 rounded bg-light" style="transition: all 0.3s;" ondrop="dropToTransferSource(event)" ondragover="allowDropTransfer(event)">
                            <p class="text-muted text-center mt-10 empty-msg-source">
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/15.png') }}" alt="اختر المادة الهدف" class="mw-100 mh-200px theme-light-show" />
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/15-dark.png') }}" alt="اختر المادة الهدف" class="mw-100 mh-200px theme-dark-show" />
                            </p>
                        </div>
                    </div>

                    <!-- Left Side: Target -->
                    <div class="col-md-6 ps-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h4 class="m-0 text-primary">الطلاب المحددين للنقل</h4>
                            <span id="transfer_target_count" class="badge badge-primary fs-7">0</span>
                        </div>
                        <div id="transfer_target_students" class="min-h-350px max-h-500px overflow-auto p-5 border border-dashed border-primary rounded bg-light-primary" style="transition: all 0.3s;" ondrop="dropToTransferTarget(event)" ondragover="allowDropTransfer(event)">
                            <p class="text-muted text-center mt-10 empty-msg">
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/17.png') }}" alt="اسحب الطلاب هنا" class="mw-100 mh-200px theme-light-show" />
                                <img src="{{ asset('assets/admin/media/illustrations/sketchy-1/17-dark.png') }}" alt="اسحب الطلاب هنا" class="mw-100 mh-200px theme-dark-show" />
                            </p>
                        </div>
                        <div class="mt-4 text-center">
                            <button class="btn btn-primary w-100 fs-5 fw-bold py-3" id="btn_transfer_enroll" onclick="submitTransferEnrolment()">
                                <i class="ki-outline ki-check-circle fs-2 me-2"></i> تنفيذ النقل / التسجيل
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let transferStudentsData = [];

    $(window).on('load', function () {
        $('#transfer_target_subject').on('change', function() {
            loadTransferStudents();
        });
    });

    function showTransferModalHelp() {
        Swal.fire({
            title: 'آلية التسجيل في مادة جديدة',
            html: `
                <ul class="fs-5 text-gray-700 text-start" style="direction: rtl;">
                    <li class="mb-2">يتم عرض جميع الطلاب المؤهلين (باستثناء المسجلين بالفعل في المادة الهدف).</li>
                    <li class="mb-2">يتم تسجيل الطالب في المادة <strong>دون اختيار مدرس أو مجموعة</strong> — التشعيب يتم لاحقاً من زر "تشعيب الطلاب" أو عبر كود التشعيب.</li>
                    <li class="mb-2">يتم ترصيد الرسوم المستحقة للمادة الجديدة وإرسال إشعار للطالب بها فوراً.</li>
                </ul>
            `,
            icon: 'info',
            confirmButtonText: 'حسناً'
        });
    }

    function fetchAllTransferStudents() {
        loadTransferStudents();
    }

    function loadTransferStudents() {
        const targetSubjectId = $('#transfer_target_subject').val();

        $.ajax({
            url: "{{ route('enrolments.students') }}",
            type: 'GET',
            data: {
                exclude_subject_id: targetSubjectId
            },
            success: function(response) {
                transferStudentsData = response;
                renderTransferSourceStudents();
            }
        });
    }

    function renderTransferSourceStudents() {
        const sourceContainer = $('#transfer_source_students');
        sourceContainer.empty();

        const targetIds = getTransferTargetStudentIds();

        if (transferStudentsData.length === 0) {
            sourceContainer.html('<p class="text-muted text-center mt-10">لا يوجد طلاب مطابقين</p>');
            updateTransferStudentCounts();
            return;
        }

        transferStudentsData.forEach(student => {
            if (targetIds.includes(student.id.toString())) return;

            let conflictClass = student.has_conflict ? 'border-danger bg-light-danger text-danger' : 'border-success bg-light-success';
            let conflictMsg = student.has_conflict ? '<span class="badge badge-danger ms-2">تعارض</span>' : '';

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
                <div id="transfer_student_${student.id}" class="d-flex align-items-center justify-content-between p-3 mb-2 border rounded cursor-pointer student-item ${conflictClass}" draggable="true" ondragstart="dragTransfer(event)">
                    <div class="d-flex align-items-center flex-grow-1" onclick="moveToTransferTarget(${student.id})">
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
                        <button type="button" class="btn btn-icon btn-sm btn-light-info ms-2" onclick="showTransferStudentDetails(${student.id}); event.stopPropagation();" title="التفاصيل">
                            <i class="ki-outline ki-eye fs-4"></i>
                        </button>
                    </div>
                </div>
            `;
            sourceContainer.append(el);
        });

        updateTransferStudentCounts();
    }

    function allowDropTransfer(ev) {
        ev.preventDefault();
    }

    function dragTransfer(ev) {
        ev.dataTransfer.setData("text", ev.currentTarget.id);
    }

    function dropToTransferTarget(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        if(data.startsWith('transfer_student_')) {
            moveToTransferTarget(data.replace('transfer_student_', ''));
        }
    }

    function dropToTransferSource(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        if(data.startsWith('transfer_student_')) {
            moveToTransferSource(data.replace('transfer_student_', ''));
        }
    }

    function moveToTransferTarget(id) {
        const el = $(`#transfer_student_${id}`);
        if(el.length) {
            const student = transferStudentsData.find(s => s.id == id);
            if (student && student.has_conflict) {
                toastr.warning('تنبيه: هذا الطالب لديه تعارض في المواعيد مع المجموعة الهدف!');
            }

            el.find('.flex-grow-1').first().attr('onclick', `moveToTransferSource(${id})`);

            if (el.find('.exclude-btn').length === 0) {
                el.find('.d-flex.align-items-center.ms-2').prepend(`
                    <button type="button" class="btn btn-icon btn-sm btn-light-danger ms-2 exclude-btn" onclick="moveToTransferSource(${id}); event.stopPropagation();" title="استثناء">
                        <i class="ki-outline ki-cross fs-4"></i>
                    </button>
                `);
            }

            $('#transfer_target_students').append(el);
            $('#transfer_target_students .empty-msg').hide();
            updateTransferStudentCounts();
        }
    }

    function moveToTransferSource(id) {
        const el = $(`#transfer_student_${id}`);
        if(el.length) {
            el.find('.flex-grow-1').first().attr('onclick', `moveToTransferTarget(${id})`);
            el.find('.exclude-btn').remove();

            $('#transfer_source_students').append(el);
            if($('#transfer_target_students .student-item').length === 0) {
                $('#transfer_target_students .empty-msg').show();
            }
            if($('#transfer_source_students .empty-msg-source').length) {
                $('#transfer_source_students .empty-msg-source').hide();
            }
            updateTransferStudentCounts();
        }
    }

    function updateTransferStudentCounts() {
        $('#transfer_source_count').text($('#transfer_source_students .student-item').length);
        $('#transfer_target_count').text($('#transfer_target_students .student-item').length);
    }

    function showTransferStudentDetails(id) {
        const student = transferStudentsData.find(s => s.id == id);
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

    function getTransferTargetStudentIds() {
        let ids = [];
        $('#transfer_target_students .student-item').each(function() {
            ids.push($(this).attr('id').replace('transfer_student_', ''));
        });
        return ids;
    }

    function submitTransferEnrolment() {
        const ids = getTransferTargetStudentIds();
        const targetSubjectId = $('#transfer_target_subject').val();

        if (ids.length === 0) {
            toastr.error('يرجى تحديد طلاب للنقل');
            return;
        }

        if (!targetSubjectId) {
            toastr.error('يرجى تحديد المادة الهدف');
            return;
        }

        const confirmText = 'سيتم تسجيل الطلاب في المادة الجديدة دون اختيار مجموعة (يتم التشعيب لاحقاً من قبل الإدارة أو عبر الكود)، مع ترصيد الرسوم المستحقة وإرسال إشعار لهم بها. هل أنت متأكد؟';

        Swal.fire({
            title: 'تأكيد النقل / التسجيل',
            text: confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، تأكيد',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('enrolments.enroll') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_ids: ids,
                        target_subject_id: targetSubjectId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('تم بنجاح!', response.message, 'success');
                            if (response.failed && response.failed.length > 0) {
                                response.failed.forEach(f => {
                                    toastr.error(`فشل نقل ${f.id}: ${f.reason}`);
                                    moveToTransferSource(f.id);
                                });
                            } else {
                                $('#transfer_target_students .student-item').remove();
                                $('#transfer_target_students .empty-msg').show();
                            }
                            loadTransferStudents();

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    },
                    error: function(err) {
                        Swal.fire('خطأ!', 'حدث خطأ أثناء النقل', 'error');
                    }
                });
            }
        });
    }
</script>
