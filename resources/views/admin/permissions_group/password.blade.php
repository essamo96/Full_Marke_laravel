@extends('admin.layout.mainLayouts.master')
@section('title')
    لوحة التحكم - تعديل كلمة السر
@stop
@section('css')
    <link href="{{ asset('assets/admin/roles/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        strong {
            color: red;
        }
    </style>
@endsection
@section('page-breadcrumb')
    <div class="col-sm-6">
        <h1>تعديل كلمة السر</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">تعديل كلمة السر</a></li>
            <li class="breadcrumb-item active">كلمة السر</li>
        </ol>
    </div>
@stop
@section('page-content')
<form action="" method="POST">
    <div class="card-body fs-6 py-15 px-10 py-lg-15 px-lg-15 text-gray-700">
            @include('admin.layout.masterLayouts.error')

            <div class="form-floating mb-7 row ">
                <div class="col">
                    <label for="floatingInput" style="margin-block: 5px;"> كلمة المرور<strong>*</strong></label>
                    <input type="password" value="{{ $info->name }}" name="password" class="form-control" id="floatingInput"
                        placeholder="كلمة المرور " />
                </div>
                <div class="col">
                    <label for="floatingInput" style="margin-block: 5px;"> تأكيد كلمة المرور<strong>*</strong></label>
                    <input type="password" value="{{ $info->username }}" class="form-control" id="floatingInput" name="password_confirmation"
                        placeholder="اعد كتابة كلمة السر" />
                </div>
            </div>
        </div>
        <div class="text-center pt-2">
            <button type="reset" class="btn btn-light me-3" data-kt-users-modal-action="cancel">اغلاق</button>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">تعديل</span>
                {{ csrf_field() }}
            </button>
        </div>

    </form>
@stop
@section('js')
    <script src="{{ asset('assets/admin/roles/assets/js/custom/apps/user-management/users/list/table.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/apps/user-management/users/list/export-users.js') }}">
    </script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/apps/user-management/users/list/add.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('assets/admin/roles/assets/js/custom/utilities/modals/users-search.js') }}"></script>

@stop
