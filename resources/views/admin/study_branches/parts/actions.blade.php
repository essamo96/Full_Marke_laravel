<a href="{{ route('study_branches.edit', \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
<form action="{{ route('study_branches.delete') }}" method="POST" class="d-inline dt-ajax-delete-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id) }}">
    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
</form>
