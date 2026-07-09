<div class="form-check form-switch form-check-custom form-check-solid">
    <input class="form-check-input status-toggle" type="checkbox" data-id="{{ Crypt::encrypt($region->id) }}" data-url="{{ route('regions.status') }}" {{ $region->status ? 'checked' : '' }} @if(!auth('admin')->user()->can('admin.regions.status')) disabled @endif />
</div>
