<a href="{{ route('users.edit', \Illuminate\Support\Facades\Crypt::encrypt($user->id)) }}" class="btn btn-sm btn-light-primary">{{ __('app.edit') }}</a>
<form action="{{ route('users.delete') }}" method="POST" class="d-inline dt-ajax-delete-form">
    @csrf
    <input type="hidden" name="id" value="{{ \Illuminate\Support\Facades\Crypt::encrypt($user->id) }}">
    <button type="submit" class="btn btn-sm btn-light-danger">{{ __('app.delete') }}</button>
</form>
