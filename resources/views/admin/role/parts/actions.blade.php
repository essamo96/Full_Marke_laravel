@can('admin.'.$active_menu.'.edit')
<a href="{{ route($active_menu.'.edit', Crypt::encrypt($id)) }}" class="btn btn-icon btn-primary btn-sm">
   <i class="bi bi-pencil-square fs-5"></i>
</a>
@endcan

@can('admin.'.$active_menu.'.permissions')
<a href="{{ route($active_menu . '.permissions',[ 'id' => Crypt::encrypt($id)]) }}" class="btn btn-icon btn-info btn-sm">
   <i class="bi bi-shield-lock-fill fs-5"></i>
</a>
@endcan

@can('admin.'.$active_menu.'.delete')
<a class="btn btn-icon btn-danger btn-sm" href="javascript:void(0)" data-href="{{ Crypt::encrypt($id) }}" data-bs-toggle="modal" data-bs-target="#confirm">
    <i class="bi bi-trash3-fill fs-5"></i>
</a>
@endcan
