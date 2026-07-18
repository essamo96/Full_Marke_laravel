@extends('admin.layout.mainLayouts.master')
@section('title', 'استعلامات الطلاب')

@section('page-content')
<div class="card mb-5 mb-xl-10">
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold fs-3 mb-1">استعلام شامل عن طالب</span>
            <span class="text-muted mt-1 fw-semibold fs-7">ابحث بالاسم، الهوية، الإيميل، أو رقم الجوال</span>
        </h3>
    </div>
    <div class="card-body py-3">
        <!-- Search Form -->
        <div class="row mb-8">
            <div class="col-md-8">
                <div class="position-relative w-100">
                    <i class="ki-duotone ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="main_inquiry_keyword" class="form-control form-control-solid ps-12" placeholder="أدخل رقم الهوية، الجوال، الإيميل أو الاسم هنا...">
                </div>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-primary w-100" onclick="performMainStudentInquiry()">
                    <i class="ki-duotone ki-search fs-2"></i> بحث
                </button>
            </div>
        </div>

        <!-- Search Results Dropdown/List -->
        <div id="main_inquiry_results" class="mb-10 d-none">
            <h5 class="mb-4 text-primary">نتائج البحث:</h5>
            <div class="table-responsive">
                <table class="table table-striped table-row-bordered gy-5 gs-7">
                    <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                            <th class="ps-4 min-w-200px rounded-start">الطالب</th>
                            <th class="min-w-150px">الهوية / الجوال</th>
                            <th class="min-w-100px  pe-4 rounded-end">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="main_inquiry_results_body">
                        <!-- Results appended here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Student Dashboard Container (Dynamically loaded) -->
<div id="student_dashboard_container">
    <!-- Student details will be rendered here -->
</div>
@endsection

@push('scripts')
<script>
function performMainStudentInquiry() {
    const keyword = $('#main_inquiry_keyword').val();
    if (!keyword || keyword.length < 2) {
        toastr.warning('يرجى إدخال كلمتي بحث على الأقل');
        return;
    }

    $('#main_inquiry_results').removeClass('d-none');
    $('#main_inquiry_results_body').html('<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-primary"></div> جاري البحث...</td></tr>');
    $('#student_dashboard_container').empty();

    $.ajax({
        url: "{{ route('student_inquiry.search') }}",
        type: 'GET',
        data: { keyword: keyword },
        success: function(response) {
            $('#main_inquiry_results_body').empty();
            if(response.length === 0) {
                $('#main_inquiry_results_body').html('<tr><td colspan="3" class="text-center text-danger py-5">لا توجد نتائج مطابقة</td></tr>');
                return;
            }
            
            response.forEach(student => {
                $('#main_inquiry_results_body').append(`
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <img src="${student.image ? '/' + student.image : '/assets/admin/media/avatars/blank.png'}" alt="" />
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="#" class="text-dark fw-bold text-hover-primary fs-6">${student.full_name_ar}</a>
                                    <span class="text-muted fw-semibold text-muted d-block fs-7">${student.email ?? ''}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-dark fw-bold d-block fs-6">${student.national_id ?? 'لا يوجد'}</span>
                            <span class="text-muted fw-semibold d-block fs-7">${student.phone ?? 'لا يوجد'}</span>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light-primary" onclick="loadStudentDashboard(${student.id})">
                                عرض التفاصيل كاملة <i class="ki-duotone ki-arrow-left ms-2 fs-5"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        },
        error: function() {
            $('#main_inquiry_results_body').html('<tr><td colspan="3" class="text-center text-danger py-5">حدث خطأ أثناء الاتصال بالخادم</td></tr>');
        }
    });
}

function loadStudentDashboard(id) {
    $('#student_dashboard_container').html('<div class="card"><div class="card-body text-center py-10"><div class="spinner-border text-primary"></div> جاري تحميل لوحة تحكم الطالب...</div></div>');
    
    $.ajax({
        url: "/admin/student-inquiry/details/" + id,
        type: 'GET',
        success: function(response) {
            if(response.success) {
                $('#student_dashboard_container').html(response.html);
                $('#main_inquiry_results').addClass('d-none'); // Hide search results after selecting
                // Re-initialize any plugins inside the new HTML if needed
                KTComponents.init();
            }
        },
        error: function() {
            toastr.error('حدث خطأ أثناء تحميل بيانات الطالب');
            $('#student_dashboard_container').empty();
        }
    });
}
</script>
@endpush
