    $(document).on('click', '.status', function () {
        var id = $(this).data('href');
        $.ajax({
            type: 'POST',
            url: '<?= route($active_menu . '.status') ?>',
            data: {
                id: id,
                _token: '{{ csrf_token() }}'
            },
            success: function (data) {
                // Controllers reply with either {status, message} (toastr keys)
                // or {success: bool} — support both instead of assuming one.
                var isSuccess = data.status ? (data.status === 'success') : !!data.success;
                var message = data.message || (isSuccess ? "{{ __('app.update_success') }}" : "{{ __('app.execution_error') }}");

                if (isSuccess) {
                    toastr.success(message);
                    table.draw(false);
                } else {
                    toastr.error(message);
                }
            },
            error: function () {
                Swal.fire({
                    title: "Oops...",
                    text: "Something went wrong!",
                    icon: "error"
                });
            }
        });
    });
    
