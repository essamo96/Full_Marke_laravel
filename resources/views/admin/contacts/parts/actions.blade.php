@can('admin.' . $active_menu . '.edit')
<a href="{{ route($active_menu . '.edit', Crypt::encrypt($id)) }}" class="btn btn-icon btn-primary btn-sm">
    <i class="bi bi-pencil-square mx-2 fs-4 me-2"></i></a>
@endcan
@can('admin.' . $active_menu . '.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($id) }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-4 me-2"></i>
</a>
@endcan