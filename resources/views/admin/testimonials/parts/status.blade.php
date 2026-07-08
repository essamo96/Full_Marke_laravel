@can('admin.'.$active_menu.'.status')
<label class="form-check form-switch">
    <input class="form-check-input status" name="status" type="checkbox" value="1" data-href="{{ Crypt::encrypt($id) }}" {{ $status == 1 ? 'checked="checked"' : '' }}>
</label>
@endcan