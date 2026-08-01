<div class="d-flex justify-content-start gap-2 flex-shrink-0">
<div class="dropdown d-inline-block">
    <button class="btn btn-sm btn-icon btn-light btn-active-light-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ki-duotone ki-dots-vertical fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
        </i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4">
        @if(is_null($student->email_verified_at) && auth('admin')->user()->hasPermissionTo('admin.pending_requests.status'))
        <li class="menu-item px-3">
            <a href="javascript:void(0)" class="menu-link px-3 text-success activate-btn" data-id="{{ \Illuminate\Support\Facades\Crypt::encrypt($student->id) }}" data-url="{{ route('pending_requests.status') }}">
                {{ \App\Helpers\translate('activate') ?? 'تفعيل الحساب' }}
            </a>
        </li>
        @endif
        @if(auth('admin')->user()->hasPermissionTo('admin.pending_requests.delete'))
        <li class="menu-item px-3">
            <a href="javascript:void(0)" class="menu-link px-3 text-danger delete-btn" data-id="{{ \Illuminate\Support\Facades\Crypt::encrypt($student->id) }}" data-url="{{ route('pending_requests.delete') }}">
                {{ \App\Helpers\translate('delete') }}
            </a>
        </li>
        @endif
    </ul>
</div>

</div>