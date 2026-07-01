<form action="{{ route('sidebar.status') }}" method="POST" class="d-inline dt-ajax-status-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($group->id) }}">
    <button type="submit" class="badge {{ $group->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
        {{ $group->status ? __('app.active') : __('app.inactive') }}
    </button>
</form>
