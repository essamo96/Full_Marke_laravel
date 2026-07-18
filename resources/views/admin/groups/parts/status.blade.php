<label class="form-check form-switch form-check-custom form-check-solid">
    <input class="form-check-input btn btn-sm btn-light-primary  status" id="status" type="checkbox" data-href="{{ Crypt::encrypt($group->id) }}" value="1" {{ $group->is_active == 1 ? 'checked' : '' }}>
</label>
