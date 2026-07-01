<form action="{{ route('programs.status') }}" method="POST" class="d-inline dt-ajax-status-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($program->id) }}">
    <button type="submit" class="badge {{ $program->is_active ? 'badge-light-success' : 'badge-light-danger' }} border-0">
        {{ $program->is_active ? __('app.active') : __('app.inactive') }}
    </button>
</form>
