<div class="form-check form-switch mt-2">
    <input class="form-check-input activeStatus" type="checkbox" data-id="{{ Crypt::encrypt($student->id) }}" value="1"
        {{ $student->status == 1 ? 'checked' : '' }} style="width: 40px; height: 20px;">
</div>
