<form action="{{ route('users.status') }}" method="POST" class="d-inline dt-ajax-status-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($user->id) }}">
    <button type="submit" class="badge {{ $user->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
        {{ $user->status ? __('app.active') : __('app.inactive') }}
    </button>
</form>
