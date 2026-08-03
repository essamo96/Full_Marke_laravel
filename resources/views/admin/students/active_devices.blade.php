@extends('admin.layout.mainLayouts.master')
@section('title')
    الطلاب المتصلون الآن
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">الطلاب المتصلون الآن</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title w-100 mb-0 d-flex justify-content-between align-items-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="onlineOnlyToggle" checked>
                                <label class="form-check-label" for="onlineOnlyToggle">إظهار المتصلين الآن فقط (آخر 5 دقائق)</label>
                            </div>
                            <span class="text-muted fs-8" id="lastRefreshedAt"></span>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <table class="table table-striped table-row-bordered gy-5 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">
                                    <th>الطالب</th>
                                    <th>الحالة</th>
                                    <th>الجهاز المرتبط</th>
                                    <th>آخر شبكة (IP)</th>
                                    <th>تاريخ ربط الجهاز</th>
                                    <th>آخر ظهور</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="activeDevicesBody">
                                <tr><td colspan="7" class="text-center text-muted py-5">جارِ التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    const listUrl = '{{ route('students.active-devices.list') }}';
    const clearIpUrl = '{{ route('students.active-devices.clear-ip') }}';
    const csrfToken = '{{ csrf_token() }}';
    let pollTimer = null;

    function renderRows(rows) {
        const tbody = document.getElementById('activeDevicesBody');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5">لا يوجد طلاب حالياً</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            const statusBadge = row.is_online
                ? '<span class="badge badge-light-success">متصل الآن</span>'
                : '<span class="badge badge-light-secondary">غير متصل</span>';
            const deviceCell = row.is_locked
                ? '<span class="badge badge-light-info">مرتبط بجهاز</span>'
                : '<span class="text-muted">— غير مرتبط —</span>';
            const ipCell = row.locked_ip
                ? '<span dir="ltr">' + row.locked_ip + '</span>'
                : '<span class="text-muted">-</span>';
            const clearBtn = row.is_locked
                ? '<button type="button" class="btn btn-sm btn-light-danger clear-ip-btn" data-id="' + row.id + '"><i class="bi bi-x-circle me-1"></i> حذف الجهاز</button>'
                : '';

            return '<tr>' +
                '<td><div class="d-flex align-items-center">' +
                    '<div class="symbol symbol-circle symbol-40px overflow-hidden me-3"><img src="' + row.image + '" alt="" class="w-100"></div>' +
                    '<div class="d-flex flex-column"><span class="fw-bold text-gray-800">' + row.name + '</span><span class="fs-8 text-muted">' + (row.email || '') + '</span></div>' +
                '</div></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' + deviceCell + '</td>' +
                '<td>' + ipCell + '</td>' +
                '<td class="text-muted fs-8">' + (row.locked_device_id_set_at || '-') + '</td>' +
                '<td class="text-muted fs-8">' + (row.last_seen_at || '-') + '</td>' +
                '<td>' + clearBtn + '</td>' +
            '</tr>';
        }).join('');
    }

    function loadList() {
        const onlineOnly = document.getElementById('onlineOnlyToggle').checked;
        fetch(listUrl + '?online_only=' + (onlineOnly ? 1 : 0), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                renderRows(data.data);
                document.getElementById('lastRefreshedAt').textContent = 'آخر تحديث: ' + new Date().toLocaleTimeString('ar-EG');
            }
        })
        .catch(function () {});
    }

    document.getElementById('onlineOnlyToggle').addEventListener('change', loadList);

    document.getElementById('activeDevicesBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.clear-ip-btn');
        if (!btn) return;
        const id = btn.dataset.id;

        Swal.fire({
            title: 'تأكيد',
            text: 'سيتم حذف الجهاز المرتبط بهذا الحساب، وسيتمكن الطالب من الدخول من أي جهاز جديد. هل تريد المتابعة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء',
            confirmButtonColor: '#d33'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(clearIpUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify({ id: id })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (typeof toastr === 'undefined') { loadList(); return; }
                data.success ? toastr.success(data.message) : toastr.error(data.message);
                loadList();
            });
        });
    });

    loadList();
    pollTimer = setInterval(loadList, 15000);
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearInterval(pollTimer);
        } else {
            loadList();
            pollTimer = setInterval(loadList, 15000);
        }
    });
</script>
@stop
