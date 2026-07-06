@if ($x == 1)
    <label class="form-check form-switch">
        <input class="form-check-input status" name="status" type="checkbox" value="1"
            data-href="{{ Crypt::encrypt($id) }}" {{ $status == 1 ? 'checked="checked"' : '' }}>
    </label>
@elseif ($x == 2)
    <a href="{{ route($active_menu . '.view') }}"
        class="btn btn-outline  btn-outline-dashed btn-outline-{{ $secondPart }} btn-active-light-{{ $secondPart }} btn-sm">{{ $name }}</a>
@elseif ($x == 3)
    <a
        class="btn btn-outline  btn-outline-dashed btn-outline-{{ $secondPart }}  btn-active-light-{{ $secondPart }} btn-sm">{{ $name }}</a>
@elseif ($x == 4)
<a href="{{$link}}">
    <div class="symbol symbol-50px symbol-circle me-5">
        <span class="symbol-label bg-light-{{ $secondPart }}">
            <i class="bi {{ $icon }} {{ $class }} fs-2x ">
            </i>
        </span>
    </div>
</a>
@endif
