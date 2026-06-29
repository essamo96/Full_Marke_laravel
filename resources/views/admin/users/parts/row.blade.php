<tr>
    <td>
        @if ($user->photo)
            <img src="{{ asset('storage/'.$user->photo) }}" class="rounded" width="40" height="40" alt="{{ $user->name }}">
        @else
            <span class="symbol symbol-40px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($user->name, 0, 1) }}</span></span>
        @endif
    </td>
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
    <td>
        <form action="{{ route('users.status') }}" method="POST" class="d-inline ajax-status-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($user->id) }}">
            <button type="submit" class="badge {{ $user->status ? 'badge-light-success' : 'badge-light-danger' }} border-0">
                {{ $user->status ? __('app.active') : __('app.inactive') }}
            </button>
        </form>
    </td>
    <td class="text-end">
        <a href="{{ route('users.edit', \Illuminate\Support\Facades\Crypt::encrypt($user->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
        <form action="{{ route('users.delete') }}" method="POST" class="d-inline ajax-delete-form">
            @csrf
            <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($user->id) }}">
            <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
        </form>
    </td>
</tr>
