@if($user->photo)
    <div class="symbol symbol-40px symbol-circle">
        <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" />
    </div>
@else
    <div class="symbol symbol-40px symbol-circle symbol-light-primary">
        <span class="symbol-label fs-1x fw-semibold text-uppercase">{{ substr($user->name, 0, 1) }}</span>
    </div>
@endif
