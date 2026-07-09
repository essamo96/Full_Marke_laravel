<div class="d-flex justify-content-end flex-shrink-0">
    @if(auth('admin')->user()->can('admin.regions.edit'))
    <a href="{{ route('regions.edit', ['id' => Crypt::encrypt($region->id)]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
        <i class="ki-duotone ki-pencil fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    @endif

    @if(auth('admin')->user()->can('admin.regions.delete'))
    <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-btn" data-id="{{ Crypt::encrypt($region->id) }}" data-url="{{ route('regions.delete') }}">
        <i class="ki-duotone ki-trash fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
    @endif
</div>
