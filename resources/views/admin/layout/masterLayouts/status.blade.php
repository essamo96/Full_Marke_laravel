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
                toastr[data.status](data.message);
            },
            error: function (error) {
                Swal.fire({
                    title: "Oops...",
                    text: "Something went wrong!",
                    icon: "error"
                });
            }
        });
    });
    
