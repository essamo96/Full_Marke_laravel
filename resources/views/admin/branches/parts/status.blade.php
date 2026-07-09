<div class="form-check form-switch form-check-custom form-check-solid">
    <input class="form-check-input status-toggle" type="checkbox" data-id="{{ Crypt::encrypt($branch->id) }}" data-url="{{ route('branches.status') }}" {{ $branch->status ? 'checked' : '' }} @if(!auth('admin')->user()->can('admin.branches.status')) disabled @endif />
</div>
