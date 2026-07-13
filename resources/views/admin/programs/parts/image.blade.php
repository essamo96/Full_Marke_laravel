@if($program->image)
    <div class="symbol symbol-50px">
        <img src="{{ str_starts_with($program->image, 'site/') ? asset($program->image) : asset('storage/' . $program->image) }}" alt="image">
    </div>
@else
    <div class="symbol symbol-50px">
        <img src="{{ asset('assets/admin/media/svg/avatars/blank.svg') }}" alt="image">
    </div>
@endif
