@if ($user->photo)
    <img src="{{ asset('storage/'.$user->photo) }}" class="rounded" width="40" height="40" alt="{{ $user->name }}">
@else
    <span class="symbol symbol-40px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($user->name, 0, 1) }}</span></span>
@endif
