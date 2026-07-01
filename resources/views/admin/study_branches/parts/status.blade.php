<form action="{{ route('study_branches.status') }}" method="POST" class="d-inline dt-ajax-status-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id) }}">
    <button type="submit" class="badge {{ $studyBranch->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
        {{ $studyBranch->status ? __('app.active') : __('app.inactive') }}
    </button>
</form>
