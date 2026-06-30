<tr>
    <td>
        <div class="fw-bold">{{ $studyBranch->name_ar }}</div>
        <div class="text-muted fs-7">{{ $studyBranch->name_en }}</div>
    </td>
    <td>
        <form action="{{ route('study_branches.status') }}" method="POST" class="d-inline ajax-status-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id) }}">
            <button type="submit" class="badge {{ $studyBranch->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                {{ $studyBranch->status ? __('app.active') : __('app.inactive') }}
            </button>
        </form>
    </td>
    <td class="text-end">
        <a href="{{ route('study_branches.edit', \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
        <form action="{{ route('study_branches.delete') }}" method="POST" class="d-inline ajax-delete-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($studyBranch->id) }}">
            <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
        </form>
    </td>
</tr>
