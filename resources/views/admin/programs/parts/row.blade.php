<tr>
    <td>
        @if ($program->image)
            <img src="{{ asset('storage/'.$program->image) }}" class="rounded" width="48" height="48" alt="{{ $program->title_en }}">
        @else
            <span class="symbol symbol-48px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($program->title_en, 0, 1) }}</span></span>
        @endif
    </td>
    <td>
        <div class="fw-bold">{{ $program->title_ar }}</div>
        <div class="text-muted fs-7">{{ $program->title_en }}</div>
    </td>
    <td><span class="badge badge-light-info">{{ __('app.type_'.$program->type) }}</span></td>
    <td>{{ $program->subjects_count }}</td>
    <td>
        <form action="{{ route('programs.status') }}" method="POST" class="d-inline ajax-status-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($program->id) }}">
            <button type="submit" class="badge {{ $program->is_active ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                {{ $program->is_active ? __('app.active') : __('app.inactive') }}
            </button>
        </form>
    </td>
    <td class="text-end">
        <a href="{{ route('programs.edit', \Illuminate\Support\Facades\Crypt::encrypt($program->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
        <form action="{{ route('programs.delete') }}" method="POST" class="d-inline ajax-delete-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($program->id) }}">
            <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
        </form>
    </td>
</tr>
