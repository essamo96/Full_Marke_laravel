@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.' . $active_menu)
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection
@section('page-content')
<?php
    // جلب المجموعات الأبوية للعرض في sortable list
    $parentGroups = \App\Models\PermissionsGroup::with(['mychild' => function($q) { $q->orderBy('sort', 'asc'); }])
        ->where('parent_id', 0)
        ->orderBy('sort', 'asc')
        ->get();
    $metronicColors = [
        'primary'   => '#009ef7',
        'success'   => '#50cd89',
        'info'      => '#7239ea',
        'warning'   => '#ffc700',
        'danger'    => '#f1416c',
        'dark'      => '#181c32',
        'muted'     => '#a1a5b7',
        'secondary' => '#e1e3ea',
    ];
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">

            {{-- ============ شريط أدوات سريعة ============ --}}
            <div class="card card-flush mb-6 shadow-sm">
                <div class="card-body py-5">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <h4 class="fw-bold mb-0">
                            <i class="bi bi-palette-fill text-success me-2"></i>{{ \App\Helpers\translate('bulk_color_change') }}
                        </h4>
                        <small class="text-muted">{{ \App\Helpers\translate('bulk_color_hint') }}</small>

                        <div class="ms-auto d-flex gap-2 flex-wrap">
                            @foreach ($metronicColors as $name => $hex)
                                <button type="button" class="btn btn-icon btn-sm bulk-color-btn"
                                        data-color="{{ $name }}"
                                        style="background-color: {{ $hex }}; border: 2px solid #fff; box-shadow: 0 0 0 1px {{ $hex }};"
                                        title="{{ $name }}">
                                </button>
                            @endforeach
                        </div>

                        <a href="{{ route($active_menu . '.add') }}" class="btn btn-primary btn-sm ms-3">
                            <i class="bi bi-plus-lg me-1"></i>{{ \App\Helpers\translate('add') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- ============ بطاقة السحب والإفلات ============ --}}
            <div class="card card-flush shadow-sm">
                <div class="card-header py-5">
                    <h3 class="card-title fw-bold">
                        <i class="bi bi-arrows-move text-primary fs-2 me-2"></i>
                        {{ \App\Helpers\translate('drag_drop_reorder') }}
                    </h3>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center position-relative me-3">
                            <i class="bi bi-search position-absolute ms-3"></i>
                            <input type="text" id="generalSearch" class="form-control form-control-sm ps-10" placeholder="{{ \App\Helpers\translate('search') }}" style="width:220px;">
                        </div>
                        <button type="button" id="save_order_btn" class="btn btn-sm btn-success" disabled>
                            <i class="bi bi-check-lg me-1"></i><span id="save_order_text">{{ \App\Helpers\translate('save_order') }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body pt-0">
                    @include('admin.layout.masterLayouts.error')

                    <ul id="sortable_groups" class="list-unstyled mb-0">
                        @foreach ($parentGroups as $group)
                            <li class="group-item border rounded mb-3 p-3 bg-body" data-id="{{ $group->id }}">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-grip-vertical fs-2 text-muted me-3 drag-handle" style="cursor:grab;"></i>
                                    <i class="bi {{ $group->icon }} fs-2 me-3 text-{{ $group->color ?: 'primary' }} group-icon"></i>
                                    <div class="flex-grow-1">
                                        <strong class="fs-5">{{ $group->{'name_' . app()->getLocale()} ?? ($group->name ?? '') }}</strong>
                                        <span class="text-muted fs-7 ms-2">({{ $group->mychild->count() }} {{ \App\Helpers\translate('sub_items') }})</span>
                                    </div>

                                    {{-- لون فردي --}}
                                    <div class="dropdown me-2">
                                        <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-circle-fill text-{{ $group->color ?: 'primary' }}"></i>
                                            {{ \App\Helpers\translate('color') }}
                                        </button>
                                        <div class="dropdown-menu p-2">
                                            @foreach ($metronicColors as $cname => $chex)
                                                <button type="button" class="btn btn-sm m-1 row-color-btn"
                                                        data-id="{{ $group->id }}"
                                                        data-enc-id="{{ \Crypt::encrypt($group->id) }}"
                                                        data-color="{{ $cname }}"
                                                        style="background:{{ $chex }}; color:#fff; width:30px; height:30px;"
                                                        title="{{ $cname }}"></button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- أزرار --}}
                                    <a href="{{ route('permissions_group.edit', \Crypt::encrypt($group->id)) }}" class="btn btn-sm btn-icon btn-light-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger delete-group-btn" data-id="{{ \Crypt::encrypt($group->id) }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                {{-- الأبناء --}}
                                @if ($group->mychild->count() > 0)
                                    <ul class="sortable-children list-unstyled mt-3 ms-5" data-parent="{{ $group->id }}">
                                        @foreach ($group->mychild as $child)
                                            <li class="child-item border rounded mb-2 p-2 bg-body" data-id="{{ $child->id }}">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-grip-vertical fs-4 text-muted me-2 drag-handle" style="cursor:grab;"></i>
                                                    <i class="bi {{ $child->icon }} fs-5 me-2 text-{{ $child->color ?: 'muted' }}"></i>
                                                    <span class="flex-grow-1">{{ $child->{'name_' . app()->getLocale()} ?? ($child->name ?? '') }}</span>
                                                    <a href="{{ route('permissions_group.edit', \Crypt::encrypt($child->id)) }}" class="btn btn-xs btn-icon btn-light-primary me-1">
                                                        <i class="bi bi-pencil fs-7"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .group-item { transition: all .2s; }
    .group-item.drag-over { border-color:#009ef7; box-shadow: 0 0 0 2px rgba(0,158,247,.2); }
    .sortable-ghost { opacity: 0.4; background: rgba(0,0,0,0.06) !important; }
    .drag-handle:active { cursor: grabbing; }
    .bulk-color-btn { width: 32px; height: 32px; border-radius: 50%; cursor: pointer; transition: transform .15s; }
    .bulk-color-btn:hover { transform: scale(1.15); }
    .row-color-btn { border-radius: 4px; border: none; }
</style>
@stop

@section('js')
{{-- Sortable JS من CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const REORDER_URL = '{{ route('permissions_group.reorder') }}';
    const UPDATE_COLOR_URL = '{{ route('permissions_group.update_color') }}';
    const BULK_COLOR_URL = '{{ route('permissions_group.bulk_color') }}';
    const DELETE_URL = '{{ route('permissions_group.delete') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    let pendingChanges = false;

    $(document).ready(function() {
        // ============ Sortable للأبوين ============
        const parentList = document.getElementById('sortable_groups');
        if (parentList) {
            Sortable.create(parentList, {
                animation: 200,
                handle: '.drag-handle',
                draggable: '.group-item',
                ghostClass: 'sortable-ghost',
                onEnd: function() { markPending(); }
            });
        }

        // ============ Sortable للأبناء ============
        document.querySelectorAll('.sortable-children').forEach(function(ul) {
            Sortable.create(ul, {
                animation: 200,
                handle: '.drag-handle',
                draggable: '.child-item',
                ghostClass: 'sortable-ghost',
                onEnd: function() { markPending(); }
            });
        });

        function markPending() {
            pendingChanges = true;
            $('#save_order_btn').prop('disabled', false).removeClass('btn-success').addClass('btn-warning');
            $('#save_order_text').text('{{ \App\Helpers\translate("save_order_pending") }}');
        }

        // ============ حفظ الترتيب ============
        $('#save_order_btn').on('click', function() {
            const items = [];

            // الأبوين
            $('#sortable_groups > .group-item').each(function(i, el) {
                items.push({ id: $(el).data('id'), sort: i + 1 });
            });

            // الأبناء
            $('.sortable-children').each(function() {
                $(this).find('> .child-item').each(function(i, el) {
                    items.push({ id: $(el).data('id'), sort: i + 1 });
                });
            });

            $.post(REORDER_URL, { _token: CSRF_TOKEN, items: items })
                .done(function(res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                        $('#save_order_btn').prop('disabled', true).removeClass('btn-warning').addClass('btn-success');
                        $('#save_order_text').text('{{ \App\Helpers\translate("save_order") }}');
                        pendingChanges = false;
                    } else {
                        Swal.fire({ icon: 'error', title: res.message });
                    }
                });
        });

        // ============ تغيير لون فردي ============
        $(document).on('click', '.row-color-btn', function() {
            const id = $(this).data('id');
            const encId = $(this).data('enc-id');
            const color = $(this).data('color');
            $.post(UPDATE_COLOR_URL, { _token: CSRF_TOKEN, id: encId, color: color })
                .done(function(res) {
                    if (res.status === 'success') {
                        const $item = $('.group-item[data-id="' + id + '"]');
                        const $icon = $item.find('.group-icon');
                        $icon.attr('class', $icon.attr('class').replace(/text-\w+/, 'text-' + color));
                        Swal.fire({ icon: 'success', title: res.message, timer: 1200, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: res.message });
                    }
                });
        });

        // ============ تغيير لون جماعي ============
        $('.bulk-color-btn').on('click', function() {
            const color = $(this).data('color');
            Swal.fire({
                title: '{{ \App\Helpers\translate("confirm_bulk_color") }}',
                text: '{{ \App\Helpers\translate("bulk_color_confirm_text") }} (' + color + ')',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '{{ \App\Helpers\translate("yes_apply") }}',
                cancelButtonText: '{{ \App\Helpers\translate("cancel") }}'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.post(BULK_COLOR_URL, { _token: CSRF_TOKEN, color: color, scope: 'parents' })
                        .done(function(res) {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: res.message,
                                    text: '{{ \App\Helpers\translate("updated") }}: ' + res.updated,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            }
                        });
                }
            });
        });

        // ============ حذف ============
        $(document).on('click', '.delete-group-btn', function() {
            const encId = $(this).data('id');
            Swal.fire({
                title: '{{ \App\Helpers\translate("confirm_delete") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ \App\Helpers\translate("yes_delete") }}',
                cancelButtonText: '{{ \App\Helpers\translate("cancel") }}',
                confirmButtonColor: '#f1416c'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.post(DELETE_URL, { _token: CSRF_TOKEN, id: encId })
                        .done(function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false })
                                    .then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: res.message });
                            }
                        });
                }
            });
        });

        // ============ Search ============
        $('#generalSearch').on('input', function() {
            const q = $(this).val().toLowerCase();
            $('.group-item').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(q) !== -1);
            });
        });

        // تحذير قبل الخروج
        window.addEventListener('beforeunload', function(e) {
            if (pendingChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
</script>
@stop
