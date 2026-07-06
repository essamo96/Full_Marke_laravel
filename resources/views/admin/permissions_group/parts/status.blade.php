<label class="form-check form-switch">
    <input class="form-check-input status" id="status" name="github" type="checkbox" value="1"
        data-href="{{ Crypt::encrypt($id) }}" {{ $status == 1 ? 'checked="checked"' : '' }}>
</label>