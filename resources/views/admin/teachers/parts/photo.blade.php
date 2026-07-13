@if($teacher->photo)
    <div class="symbol symbol-50px symbol-circle">
        <img src="{{ asset('storage/' . $teacher->photo) }}" alt="image">
    </div>
@else
    <div class="symbol symbol-50px symbol-circle">
        <img src="{{ asset('assets/admin/media/svg/avatars/blank.svg') }}" alt="image">
    </div>
@endif
