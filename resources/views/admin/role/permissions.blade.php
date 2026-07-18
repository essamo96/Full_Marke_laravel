@extends('admin.layout.mainLayouts.master')
@section('title')
    {{ $current_route->{'name_' . app()->getLocale()} }}
@stop

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h2>{{ $current_route->{'name_' . app()->getLocale()} }}</h2>
                        </div>
                    </div>

                    <div class="card-body py-4">
                        @include('admin.layout.masterLayouts.error')

                        <form id="kt_modal_add_user_form" class="form" action="" method="POST">
                            {{ csrf_field() }}

                            <div class="accordion" id="permissionAccordion">
                                @foreach ($permission_group as $group)
                                    <div class="accordion-item mb-4">
                                        <h2 class="accordion-header" id="heading_{{ $group->id }}">
                                            <button class="accordion-button fs-4 fw-bold px-5 py-4 collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse_{{ $group->id }}"
                                                aria-expanded="false" aria-controls="collapse_{{ $group->id }}">
                                                <i class="{{ $group->icon ?? 'bi bi-diagram-3' }} fs-2 text-{{ $group->color ?? 'dark' }} me-3"></i>
                                                {{ $group->{'name_' . app()->getLocale()} ?? $group->name }}
                                            </button>
                                        </h2>
                                        <div id="collapse_{{ $group->id }}" class="accordion-collapse collapse"
                                            aria-labelledby="heading_{{ $group->id }}" data-bs-parent="#permissionAccordion">
                                            <div class="accordion-body p-5">

                                                <div class="d-flex align-items-center mb-5">
                                                    <div class="form-check form-check-custom form-check-{{ $group->color ?? 'warning' }} form-check-solid me-3">
                                                        <input class="form-check-input group-select-all " type="checkbox"
                                                            id="group_all_{{ $group->id }}" data-group="{{ $group->id }}">
                                                    </div>
                                                    <label class="form-check-label fw-bold" for="group_all_{{ $group->id }}">
                                                        @lang('تحديد الكل') ({{ $group->permissions->count() }} @lang('صلاحية'))
                                                    </label>
                                                </div>

                                                <div class="row">
                                                    @foreach($group->permissions as $perm)
                                                        <div class="col-md-4 col-sm-6 mb-3">
                                                            <div class="form-check form-check-custom form-check-{{ $group->color ?? 'dark' }} form-check-solid">
                                                                <input class="form-check-input group-perm-{{ $group->id }}"
                                                                    type="checkbox" name="permissions[]"
                                                                    id="perm_{{ $perm->id }}"
                                                                    value="{{ $perm->id }}"
                                                                    data-group="{{ $group->id }}"
                                                                    {{ in_array($perm->id, array_column($role_permissions, 'permission_id')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                                    @php
                                                                        $parts = explode('.', $perm->name);
                                                                        $viewPart = end($parts);
                                                                    @endphp
                                                                    @lang($viewPart)
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-center pt-10">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <span class="indicator-label">@lang('حفظ')</span>
                                </button>
                                <a href="{{ route($active_menu . '.view') }}" class="btn btn-sm btn-light me-3">
                                    @lang('إلغاء الأمر')
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // عند الضغط على "تحديد الكل"
        document.querySelectorAll('.group-select-all').forEach(function(master) {
            master.addEventListener('change', function() {
                const groupId = this.dataset.group;
                const checked = this.checked;
                document.querySelectorAll('.group-perm-' + groupId).forEach(function(cb) {
                    cb.checked = checked;
                });
            });
        });

        // تحديث حالة "تحديد الكل" عند تغيير أي صلاحية
        document.querySelectorAll('[name="permissions[]"]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                const groupId = this.dataset.group;
                const all = Array.from(document.querySelectorAll('.group-perm-' + groupId));
                const master = document.querySelector('#group_all_' + groupId);
                if (!master) return;
                master.checked = all.length > 0 && all.every(i => i.checked);
            });
        });

        // تحديث مبدئي عند تحميل الصفحة (للحالات edit)
        document.querySelectorAll('.accordion-body').forEach(function(body) {
            const groupId = body.querySelector('.group-select-all').dataset.group;
            const all = Array.from(document.querySelectorAll('.group-perm-' + groupId));
            const master = document.querySelector('#group_all_' + groupId);
            if (!master) return;
            master.checked = all.length > 0 && all.every(i => i.checked);
        });
    });
</script>

@stop
