{{--
    Reusable Yajra DataTables (server-side AJAX) initializer.
    Usage: @include('admin.components.datatable-init', [
        'tableId' => 'sidebar_table',
        'ajaxUrl' => route('sidebar.list'),
        'columns' => [
            ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            ['data' => 'parent', 'name' => 'parent_id', 'title' => __('app.parent_group'), 'orderable' => false, 'searchable' => false],
            ['data' => 'status', 'name' => 'status', 'title' => __('app.status'), 'orderable' => false, 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'title' => __('app.actions'), 'orderable' => false, 'searchable' => false],
        ],
    ])
--}}
@push('scripts')
    <script>
        $(function () {
            var table = $('#{{ $tableId }}').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searching: false,
                order: [],
                ajax: {
                    url: @json($ajaxUrl),
                    data: function (d) {
                        d.search_value = $('#search-filter-input').val();
                    },
                },
                columns: @json($columns),
                columnDefs: [{ targets: '_all', orderable: false }],
                language: {
                    url: @json(app()->getLocale() === 'ar'
                        ? 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                        : ''),
                },
            });

            var searchTimer;
            $('#search-filter-input').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { table.draw(); }, 400);
            });

            document.addEventListener('click', function (e) {
                var statusBtn = e.target.closest('.dt-ajax-status-form button, .dt-ajax-status-form');
                var deleteBtn = e.target.closest('.dt-ajax-delete-form button, .dt-ajax-delete-form');

                if (statusBtn) {
                    var form = statusBtn.closest('form');
                    if (!form) return;
                    e.preventDefault();
                    fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            if (res.success) { table.draw(false); } else { Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' }); }
                        });
                }

                if (deleteBtn) {
                    var form = deleteBtn.closest('form');
                    if (!form) return;
                    e.preventDefault();
                    Swal.fire({
                        text: '{{ __('app.confirm_delete') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: '{{ __('app.yes_delete') }}',
                        cancelButtonText: '{{ __('app.cancel') }}',
                        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-light' },
                    }).then(function (result) {
                        if (!result.isConfirmed) return;
                        fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(form) })
                            .then(function (r) { return r.json(); })
                            .then(function (res) {
                                if (res.success) {
                                    Swal.fire({ text: '{{ __('app.delete_success') }}', icon: 'success', timer: 1200, showConfirmButton: false })
                                        .then(function () { table.draw(false); });
                                } else {
                                    Swal.fire({ text: '{{ __('app.execution_error') }}', icon: 'error' });
                                }
                            });
                    });
                }
            });
        });
    </script>
@endpush
