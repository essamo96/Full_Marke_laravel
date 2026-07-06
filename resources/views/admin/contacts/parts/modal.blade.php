<div class="modal fade" id="confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="alert alert-dismissible bg-light-danger d-flex flex-center flex-column py-10 px-10 px-lg-20 mb-10 ">

                <button type="button" class="position-absolute top-0 end-0 m-2 btn btn-icon btn-icon-danger"
                    data-bs-dismiss="alert">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <i class="ki-duotone ki-information-5 fs-5tx text-danger mb-5"><span class="path1"></span><span
                        class="path2"></span><span class="path3"></span></i>
                <div class="text-center">
                    <h1 class="fw-bold mb-5">{{ \App\Helpers\translate('warning') }}</h1>
                    <div class="separator separator-dashed border-danger opacity-25 mb-5"></div>
                    <div class="mb-9 text-dark">{{ \App\Helpers\translate('comfairm') }}
                        <strong>{{ \App\Helpers\translate('delete_footer') }}</strong>.<br />
                    </div>
                    <div class="d-flex flex-center flex-wrap">
                        <a type="button" class="btn btn-outline btn-outline-danger btn-active-danger m-2">{{ \App\Helpers\translate('no') }}</a>
                        <a class="btn btn-danger m-2 delete" type="button">{{ \App\Helpers\translate('yes') }}</a>
                        <input type="hidden" id="delete_id">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
