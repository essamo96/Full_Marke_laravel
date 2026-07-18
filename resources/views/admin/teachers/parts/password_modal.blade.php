<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">تغيير كلمة المرور - <span id="changePasswordTeacherName"></span></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="changePasswordForm" class="form" action="{{ route('teachers.change_password') }}">
                    @csrf
                    <input type="hidden" name="id" id="changePasswordTeacherId">
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="fs-6 fw-semibold mb-2">
                            <span class="required">كلمة المرور الجديدة</span>
                        </label>
                        <input type="password" class="form-control form-control-solid" name="password" id="modal_password" required minlength="8" />
                    </div>
                    <div class="d-flex flex-column mb-7 fv-row">
                        <label class="fs-6 fw-semibold mb-2">
                            <span class="required">تأكيد كلمة المرور</span>
                        </label>
                        <input type="password" class="form-control form-control-solid" name="password_confirmation" id="modal_password_confirmation" required minlength="8" />
                        <span id="modal_password_match_message" class="text-danger mt-2 fs-8" style="display:none;"></span>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">@lang('app.cancel')</button>
                        <button type="submit" class="btn btn-primary" id="changePasswordSubmit">
                            <span class="indicator-label">@lang('app.save')</span>
                            <span class="indicator-progress">الرجاء الانتظار... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var changePasswordModal = document.getElementById('changePasswordModal');
    if(changePasswordModal) {
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            
            document.getElementById('changePasswordTeacherId').value = id;
            document.getElementById('changePasswordTeacherName').textContent = name;
            document.getElementById('changePasswordForm').reset();
            $('#modal_password_match_message').hide();
        });

        $('#modal_password, #modal_password_confirmation').on('keyup', function() {
            var password = $('#modal_password').val();
            var confirmPassword = $('#modal_password_confirmation').val();
            var message = $('#modal_password_match_message');
            
            if (password !== '' && confirmPassword !== '') {
                if (password !== confirmPassword) {
                    message.text('كلمتا المرور غير متطابقتين').show();
                    message.removeClass('text-success').addClass('text-danger');
                } else {
                    message.text('كلمتا المرور متطابقتان').show();
                    message.removeClass('text-danger').addClass('text-success');
                }
            } else {
                message.hide();
            }
        });

        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            if ($('#modal_password').val() !== $('#modal_password_confirmation').val()) {
                $('#modal_password_match_message').text('كلمتا المرور غير متطابقتين').show();
                $('#modal_password_match_message').removeClass('text-success').addClass('text-danger');
                return;
            }

            var form = $(this);
            var submitBtn = $('#changePasswordSubmit');
            
            submitBtn.attr('data-kt-indicator', 'on');
            submitBtn.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    submitBtn.removeAttr('data-kt-indicator');
                    submitBtn.prop('disabled', false);
                    if(response.success) {
                        $('#changePasswordModal').modal('hide');
                        Swal.fire({
                            text: response.message || "تم تغيير كلمة المرور بنجاح",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "حسناً",
                            customClass: { confirmButton: "btn btn-primary" }
                        });
                    }
                },
                error: function(xhr) {
                    submitBtn.removeAttr('data-kt-indicator');
                    submitBtn.prop('disabled', false);
                    var msg = "حدث خطأ ما";
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if(xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        text: msg,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "حسناً",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                }
            });
        });
    }
});
</script>
