@php
    $hasStatusColumn = false;

    if (!empty($columns) && is_array($columns)) {
        foreach ($columns as $col) {
            if ((isset($col['data']) && $col['data'] === 'status') ||
                (isset($col['name']) && $col['name'] === 'status')) {
                $hasStatusColumn = true;
                break;
            }
        }
    }
@endphp

$(document).ready(function() {
    const dataTableLanguageUrl = "{{ route('datatables.lang', ['locale' => app()->getLocale()]) }}";

    var tableSelector = (typeof tableId !== 'undefined' && tableId) ? '#' + tableId : '#kt_table';

    // تهيئة الفلاتر إذا موجودة
    if (typeof filterFields !== 'undefined' && filterFields.length > 0) {
        filterFields.forEach(function(field) {
            if ($(field).is("input[type='date'], .datepicker")) {
                $(field).flatpickr();
            }
        });
    }

    table = $(tableSelector).DataTable({
        responsive: true,
        ordering: false,
        processing: true,
        pageLength: 10,
        bLengthChange: true,
        bFilter: false,
        serverSide: true,
        stateSave: true,
        dom: "<'row'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'l><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'f>>" +
             "<'table-responsive'tr>" +
             "<'row'<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i><'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>>",
        ajax: {
            url: '{{ route($active_menu . '.list') }}',
            data: function(d) {
                if (typeof filterFields !== 'undefined') {
                    filterFields.forEach(function(field) {
                        let key = $(field).attr('name') || $(field).attr('id');
                        d[key] = $(field).val();
                    });
                }
                d.name = $('#generalSearch').val();
                d.companies = $('#companies').val();
            }
        },
        columns: columns,
        language: { url: dataTableLanguageUrl },
        createdRow: function(row, data, dataIndex) {
            $(row).find('td:eq(1)').addClass('d-flex align-items-center');
        }
    });

    if (typeof filterFields !== 'undefined') {
        $(filterFields.join(',')).on('change keyup', function() {
            table.draw();
        });
    }
    $('#generalSearch, #companies').on('input change', function () {
         table.ajax.reload(null, false);
    });
    var hasStatusColumn = columns.some(col => col.data === 'status');

    @include('admin.layout.masterLayouts.delete')

        if (hasStatusColumn) {
            @include('admin.layout.masterLayouts.status')
        }
});
