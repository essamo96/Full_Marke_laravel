@if ($x == 1)
    <label class="form-check form-switch">
        <input class="form-check-input status" name="status" type="checkbox" value="1"
            data-href="{{ Crypt::encrypt($id) }}" {{ $status == 1 ? 'checked="checked"' : '' }}>
    </label>
@elseif ($x == 2)
           <a href="{{route($active_menu.'.view')}}" class="btn btn-outline  btn-outline-dashed btn-outline-info btn-active-light-info btn-sm">{{$name}}</a>
@elseif ($x == 3)
    <a class="btn btn-outline  btn-outline-dashed btn-outline-warning  btn-active-light-info btn-sm">{{$name}}</a>

@endif
