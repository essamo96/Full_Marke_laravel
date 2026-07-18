// Handles the simpler action-button pattern used by some screens
// (regions, branches, payment methods, pending requests, ...): a bare
// [data-id][data-url] button/checkbox with no dedicated #confirm modal,
// as opposed to the older `.delete` / `.status` + #confirm pattern above.

$(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var url = btn.data('url');

    Swal.fire({
        title: 'تحذير !',
        text: 'هل أنت متأكد من حذف البيانات بشكل نهائي؟ لا يمكن إسترجاع البيانات.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#d33'
    }).then(function (result) {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function (data) {
                var isSuccess = data.status ? (data.status === 'success') : (data.success !== false);
                var message = data.message || (isSuccess ? "{{ __('app.delete_success') }}" : "{{ __('app.execution_error') }}");

                if (isSuccess) {
                    toastr.success(message);
                    table.draw(false);
                } else {
                    toastr.error(message);
                }
            },
            error: function (xhr) {
                var message = (xhr.responseJSON && xhr.responseJSON.message) || "{{ __('app.execution_error') }}";
                toastr.error(message);
            }
        });
    });
});

$(document).on('change', '.status-toggle', function () {
    var checkbox = $(this);
    var id = checkbox.data('id');
    var url = checkbox.data('url');
    var previousState = !checkbox.prop('checked');

    checkbox.prop('disabled', true);

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id: id,
            _token: '{{ csrf_token() }}'
        },
        success: function (data) {
            var isSuccess = data.status ? (data.status === 'success') : (data.success !== false);
            var message = data.message || (isSuccess ? "{{ __('app.update_success') }}" : "{{ __('app.execution_error') }}");

            if (isSuccess) {
                toastr.success(message);
            } else {
                checkbox.prop('checked', previousState);
                toastr.error(message);
            }
        },
        error: function (xhr) {
            checkbox.prop('checked', previousState);
            var message = (xhr.responseJSON && xhr.responseJSON.message) || "{{ __('app.execution_error') }}";
            toastr.error(message);
        },
        complete: function () {
            checkbox.prop('disabled', false);
        }
    });
});
