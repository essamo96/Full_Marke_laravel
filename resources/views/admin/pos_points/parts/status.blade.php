@can('admin.pos_points.status')
<div class="form-check form-switch mt-2">
    <input class="form-check-input status" type="checkbox" data-href="{{ Crypt::encrypt($point->id) }}" value="1"
        {{ $point->is_active == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
</div>
@endcan
