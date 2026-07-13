<div class="form-check form-switch mt-2">
    <input class="form-check-input activeStatus" type="checkbox" data-id="{{ Crypt::encrypt($program->id) }}" value="1"
        {{ $program->is_active == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
</div>
