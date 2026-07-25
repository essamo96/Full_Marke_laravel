@if($point->image)
    <div class="symbol symbol-50px">
        <img src="{{ str_starts_with($point->image, 'site/') ? asset($point->image) : asset('storage/' . $point->image) }}" alt="image">
    </div>
@else
    <div class="symbol symbol-50px">
        <img src="{{ asset('assets/admin/media/svg/avatars/blank.svg') }}" alt="image">
    </div>
@endif
