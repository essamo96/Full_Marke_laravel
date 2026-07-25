@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.' . $active_menu)
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <span class="text-muted">@lang('app.' . $active_menu)</span>
    </li>
@endsection

@section('page-content')
    <style>
        .fm-file-item:hover .fm-file-name { color: var(--bs-primary) !important; cursor: pointer; }
        .fm-row { transition: background-color .15s ease; }
        .fm-row:hover { background-color: var(--bs-light); }
    </style>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                {{-- Header --}}
                <div class="card card-flush pb-0 mb-6">
                    <div class="card-header pt-8">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-circle me-5">
                                <div class="symbol-label bg-light-primary text-primary">
                                    <i class="ki-duotone ki-folder fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <h2 class="mb-1">@lang('app.file_manager')</h2>
                                <div class="text-muted fw-bold">
                                    @if($picker)
                                        <span class="text-primary">@lang('app.pick_a_file_hint')</span>
                                    @else
                                        {{ count($directories) + count($files) }} @lang('app.items')
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main card --}}
                <div class="card card-flush">
                    <div class="card-header pt-8">
                        <div class="card-title">
                            <div class="d-flex align-items-center position-relative my-1">
                                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" id="fm_search" class="form-control form-control-solid w-250px ps-13" placeholder="@lang('app.search_placeholder')" />
                            </div>
                        </div>
                        <div class="card-toolbar">
                            @can('admin.file_manager.add')
                            <button type="button" class="btn btn-flex btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#fm_modal_new_folder">
                                <i class="ki-duotone ki-add-folder fs-2"><span class="path1"></span><span class="path2"></span></i> @lang('app.new_folder')
                            </button>
                            <button type="button" class="btn btn-flex btn-primary" data-bs-toggle="modal" data-bs-target="#fm_modal_upload">
                                <i class="ki-duotone ki-folder-up fs-2"><span class="path1"></span><span class="path2"></span></i> @lang('app.upload_files')
                            </button>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Breadcrumb --}}
                        <div class="d-flex flex-stack mb-5 flex-wrap gap-3">
                            <div class="badge badge-lg badge-light-primary py-3 px-5">
                                <div class="d-flex align-items-center flex-wrap">
                                    <i class="ki-duotone ki-abstract-32 fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                                    @foreach($breadcrumbs as $crumb)
                                        <a href="{{ route('file_manager.view', array_filter(['folder' => $crumb['path'], 'picker' => $picker ? 1 : null, 'target' => $picker ? $target : null])) }}" class="text-primary fw-bold text-hover-dark">{{ $crumb['name'] }}</a>
                                        @if(!$loop->last)
                                            <i class="ki-duotone ki-right fs-4 text-primary mx-2"><span class="path1"></span><span class="path2"></span></i>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="badge badge-lg badge-primary py-3 px-5">
                                {{ count($directories) + count($files) }} @lang('app.items')
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="fm_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-250px">@lang('app.name')</th>
                                        <th class="min-w-100px">@lang('app.size')</th>
                                        <th class="min-w-125px">@lang('app.last_modified')</th>
                                        <th class="w-125px text-end pe-7">@lang('app.actions')</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @foreach($directories as $dir)
                                    <tr class="fm-row">
                                        <td data-order="{{ $dir['name'] }}">
                                            <a href="{{ route('file_manager.view', array_filter(['folder' => $dir['path'], 'picker' => $picker ? 1 : null, 'target' => $picker ? $target : null])) }}" class="d-flex align-items-center text-gray-800 text-hover-primary fm-file-item">
                                                <div class="symbol symbol-40px me-4">
                                                    <div class="symbol-label bg-light-primary">
                                                        <i class="ki-duotone ki-folder fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                    </div>
                                                </div>
                                                <span class="fw-bold fm-file-name">{{ $dir['name'] }}</span>
                                            </a>
                                        </td>
                                        <td>-</td>
                                        <td>{{ $dir['last_modified'] }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                @can('admin.file_manager.edit')
                                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-2 btn-rename" data-path="{{ $dir['path'] }}" data-type="folder" data-name="{{ $dir['name'] }}" title="@lang('app.rename')">
                                                    <i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                @endcan
                                                @can('admin.file_manager.delete')
                                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-danger btn-delete" data-path="{{ $dir['path'] }}" data-type="folder" title="@lang('app.delete')">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    @foreach($files as $file)
                                    <tr class="fm-row">
                                        <td data-order="{{ $file['name'] }}">
                                            @if($picker)
                                            <a href="javascript:void(0)" class="d-flex align-items-center text-gray-800 text-hover-primary fm-file-item fm-pick-file" data-url="{{ $file['url'] }}" data-path="{{ $file['path'] }}">
                                            @else
                                            <a href="{{ $file['url'] }}" target="_blank" class="d-flex align-items-center text-gray-800 text-hover-primary fm-file-item">
                                            @endif
                                                <div class="symbol symbol-40px me-4">
                                                    <div class="symbol-label bg-light">
                                                        @if(in_array($file['extension'], ['jpg','jpeg','png','webp','gif','bmp']))
                                                            <img src="{{ $file['url'] }}" class="h-30px" alt="{{ $file['name'] }}" style="border-radius:4px;object-fit:cover;" />
                                                        @elseif($file['extension'] == 'pdf')
                                                            <i class="ki-duotone ki-file-down fs-2x text-danger"><span class="path1"></span><span class="path2"></span></i>
                                                        @elseif(in_array($file['extension'], ['doc','docx']))
                                                            <i class="ki-duotone ki-file-added fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                        @elseif(in_array($file['extension'], ['xls','xlsx','csv']))
                                                            <i class="ki-duotone ki-file-added fs-2x text-success"><span class="path1"></span><span class="path2"></span></i>
                                                        @elseif(in_array($file['extension'], ['ppt','pptx']))
                                                            <i class="ki-duotone ki-file-added fs-2x text-warning"><span class="path1"></span><span class="path2"></span></i>
                                                        @elseif(in_array($file['extension'], ['zip','rar','7z']))
                                                            <i class="ki-duotone ki-archive fs-2x text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                        @else
                                                            <i class="ki-duotone ki-file fs-2x text-success"><span class="path1"></span><span class="path2"></span></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="fw-bold fm-file-name">{{ $file['name'] }}</span>
                                                @if($picker)
                                                    <span class="badge badge-light-primary ms-3">@lang('app.select')</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td>{{ $file['size'] }}</td>
                                        <td>{{ $file['last_modified'] }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-2 btn-copy-url" data-url="{{ $file['url'] }}" title="@lang('app.copy_link')">
                                                    <i class="ki-duotone ki-fasten fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                @can('admin.file_manager.edit')
                                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary me-2 btn-rename" data-path="{{ $file['path'] }}" data-type="file" data-name="{{ $file['name'] }}" title="@lang('app.rename')">
                                                    <i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                                @endcan
                                                @can('admin.file_manager.delete')
                                                <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-danger btn-delete" data-path="{{ $file['path'] }}" data-type="file" title="@lang('app.delete')">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach

                                    @if($directories->isEmpty() && $files->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-10">
                                            <i class="ki-duotone ki-folder fs-3x text-muted d-block mb-3"><span class="path1"></span><span class="path2"></span></i>
                                            @lang('app.empty_folder')
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Upload modal --}}
    <div class="modal fade" id="fm_modal_upload" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">@lang('app.upload_files')</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body pt-10 pb-15 px-lg-17">
                    <div class="dropzone" id="fm_dropzone">
                        <div class="dz-message needsclick">
                            <i class="ki-duotone ki-file-up fs-3hx text-primary"><span class="path1"></span><span class="path2"></span></i>
                            <div class="ms-4">
                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">@lang('app.drop_files_here')</h3>
                                <span class="fs-7 fw-semibold text-gray-400">@lang('app.upload_up_to_10')</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- New folder modal --}}
    <div class="modal fade" id="fm_modal_new_folder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <form class="form" id="fm_new_folder_form">
                    <div class="modal-header">
                        <h2 class="fw-bold">@lang('app.new_folder')</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-9">
                            <label class="fs-6 fw-semibold mb-2 required">@lang('app.folder_name')</label>
                            <input type="text" class="form-control form-control-solid" name="name" id="fm_new_folder_name" required />
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="submit" class="btn btn-primary">@lang('app.save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function () {
        const currentFolder = @json($currentFolder);
        const isPicker = @json($picker);
        const pickerTarget = @json($target);

        if (isPicker) {
            $(document).on('click', '.fm-pick-file', function () {
                const url = $(this).data('url');
                const path = $(this).data('path');
                if (window.opener && typeof window.opener.fmPickerCallback === 'function') {
                    window.opener.fmPickerCallback(url, path, pickerTarget);
                }
                window.close();
            });
        }

        // Client-side search filter (small folder listings, no server pagination needed)
        $('#fm_search').on('keyup', function () {
            const q = $(this).val().toLowerCase();
            $('#fm_table tbody tr').each(function () {
                const name = $(this).find('.fm-file-name').text().toLowerCase();
                $(this).toggle(name.indexOf(q) > -1);
            });
        });

        if ($('#fm_dropzone').length && typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
            new Dropzone('#fm_dropzone', {
                url: "{{ route('file_manager.upload') }}",
                paramName: 'file',
                maxFiles: 10,
                maxFilesize: 20,
                addRemoveLinks: true,
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                sending: function (file, xhr, formData) {
                    formData.append('folder', currentFolder);
                },
                success: function () {
                    if (typeof toastr !== 'undefined') toastr.success("{{ __('app.insert_success') }}");
                },
                error: function (file, message) {
                    if (typeof toastr !== 'undefined') toastr.error(typeof message === 'string' ? message : "{{ __('app.execution_error') }}");
                },
                queuecomplete: function () {
                    setTimeout(function () { window.location.reload(); }, 800);
                },
            });
        }

        $('#fm_new_folder_form').on('submit', function (e) {
            e.preventDefault();
            $.post("{{ route('file_manager.create_folder') }}", {
                _token: "{{ csrf_token() }}",
                name: $('#fm_new_folder_name').val(),
                folder: currentFolder,
            }, function (res) {
                if (res.success) window.location.reload();
            }).fail(function (xhr) {
                if (typeof toastr !== 'undefined') toastr.error(xhr.responseJSON?.message || "{{ __('app.execution_error') }}");
            });
        });

        $(document).on('click', '.btn-delete', function () {
            const path = $(this).data('path');
            const type = $(this).data('type');
            Swal.fire({
                title: "{{ __('app.warning') }}",
                text: "{{ __('app.nota_delete') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: "{{ __('app.yes') }}",
                cancelButtonText: "{{ __('app.no') }}",
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.post("{{ route('file_manager.delete') }}", { _token: "{{ csrf_token() }}", path: path, type: type }, function (res) {
                        if (res.success) window.location.reload();
                    });
                }
            });
        });

        $(document).on('click', '.btn-rename', function () {
            const path = $(this).data('path');
            const type = $(this).data('type');
            const name = $(this).data('name');
            Swal.fire({
                title: "{{ __('app.rename') }}",
                input: 'text',
                inputValue: name,
                showCancelButton: true,
                confirmButtonText: "{{ __('app.save') }}",
                cancelButtonText: "{{ __('app.cancel') }}",
            }).then(function (result) {
                if (result.isConfirmed && result.value) {
                    $.post("{{ route('file_manager.rename') }}", { _token: "{{ csrf_token() }}", path: path, name: result.value, type: type }, function (res) {
                        if (res.success) window.location.reload();
                    });
                }
            });
        });

        $(document).on('click', '.btn-copy-url', function () {
            const url = $(this).data('url');
            navigator.clipboard.writeText(url).then(function () {
                if (typeof toastr !== 'undefined') toastr.success("{{ __('app.link_copied') }}");
            });
        });
    });
</script>
@stop
