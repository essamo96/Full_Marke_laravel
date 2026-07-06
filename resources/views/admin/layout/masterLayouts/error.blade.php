@if (session('type') == 'success')
    <script>
        Swal.fire({
            position: 'top-end',
            icon: '{{ session('type') }}',
            title: '{{ session('type') }}',
            showConfirmButton: false,
            text: '{{ session('message') }}',
            timer: 2500
        })
    </script>
@elseif(session('type') == 'danger')
    <script>
        Swal.fire({
            icon: '{{ session('icon') }}',
            title: '{{ session('type') == 'danger' ? 'danger!' : 'warning!' }}',
            text: '{!! session('message') !!}',
            confirmButtonText: 'OK'
        })
    </script>
@endif
@if (Session::has('success'))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-success">
                <button type="button"
                    class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                    data-bs-dismiss="alert">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>
                @if (is_object(Session::get('success')))
                    @foreach (Session::get('success')->all(':message') as $message)
                        {{ $message }}
                    @endforeach
                @else
                    {{ Session::get('success') }}
                @endif
            </div>
        </div>
    </div>
@elseif(Session::has('errors'))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger">
                <button type="button"
                    class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                    data-bs-dismiss="alert">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>
                @if (is_object(Session::get('errors')))
                    @foreach (Session::get('errors')->all(':message') as $message)
                        <ul>{!! $message !!}</ul>
                    @endforeach
                @else
                    {{ Session::get('errors') }}
                @endif
            </div>
        </div>
    </div>
@elseif(Session::has('danger'))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger">
                <button type="button"
                    class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                    data-bs-dismiss="alert">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>
                @if (is_object(Session::get('danger')))
                    @foreach (Session::get('danger')->all(':message') as $message)
                        <ul>{!! $message !!}</ul>
                    @endforeach
                @else
                    {{ Session::get('danger') }}
                @endif
            </div>
        </div>
    </div>
@elseif(Session::has('warning'))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>

                @if (is_object(Session::get('warning')))
                    @foreach (Session::get('warning')->all(':message') as $message)
                        {!! $message !!}
                    @endforeach
                @else
                    {{ Session::get('warning') }}
                @endif
            </div>
        </div>
    </div>
@elseif(Session::has('info'))
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">×</span>
                    <span class="sr-only">Close</span>
                </button>

                @if (is_object(Session::get('info')))
                    @foreach (Session::get('info')->all(':message') as $message)
                        {{ $message }}
                    @endforeach
                @else
                    {{ Session::get('info') }}
                @endif
            </div>
        </div>
    </div>
@endif
