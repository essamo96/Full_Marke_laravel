<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">@lang('app.change_password')</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="bi bi-x fs-1"></i>
                </div>
            </div>
            <form id="changePasswordForm" method="POST" action="{{ route('users.password.submit') }}">
                @csrf
                <input type="hidden" name="id" id="password_user_id">
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2 required">
                            <span>@lang('app.password')</span>
                        </label>
                        <input type="password" class="form-control form-control-solid" placeholder="" name="password" required />
                    </div>
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="d-flex align-items-center fs-6 fw-semibold form-label mb-2 required">
                            <span>@lang('app.confirm_password')</span>
                        </label>
                        <input type="password" class="form-control form-control-solid" placeholder="" name="password_confirmation" required />
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">@lang('app.cancel')</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">@lang('app.save')</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#changePasswordModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);
            modal.find('#password_user_id').val(id);
        });

        $('#changePasswordForm').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        $('#changePasswordModal').modal('hide');
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "حسنًا",
                            customClass: { confirmButton: "btn btn-primary" }
                        });
                        form[0].reset();
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "حسنًا",
                            customClass: { confirmButton: "btn btn-danger" }
                        });
                    }
                },
                error: function (xhr) {
                    var msg = "حدث خطأ ما";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        text: msg,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "حسنًا",
                        customClass: { confirmButton: "btn btn-danger" }
                    });
                }
            });
        });
    });
</script>
