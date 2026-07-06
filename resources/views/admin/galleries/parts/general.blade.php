@if ($x == 1)
    <label class="form-check form-switch">
        <input class="form-check-input status" name="status" type="checkbox" value="1"
            data-href="{{ Crypt::encrypt($id) }}" {{ $status == 1 ? 'checked="checked"' : '' }}>
    </label>
@elseif ($x == 2)
           <a href="{{route($active_menu.'.view')}}" class="btn btn-outline  btn-outline-dashed btn-outline-info btn-active-light-info btn-sm">{{$name}}</a>
@elseif ($x == 3)
    <a class="btn btn-outline  btn-outline-dashed btn-outline-warning  btn-active-light-info btn-sm">{{$name}}</a>
@elseif ($x == 4)
    @if($image)
        <button type="button" class="btn btn-outline btn-outline-dashed btn-outline-warning btn-active-light-warning btn-sm"
                data-bs-toggle="modal" data-bs-target="#imageModal-{{ $id }}">
            <i class="bi bi-eye-fill"></i> {{ \App\Helpers\translate('view_image') }}
        </button>

        <!-- Modal -->
        <div class="modal fade" id="imageModal-{{ $id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel-{{ $id }}">{{ \App\Helpers\translate('image') }}</h5>
                        <div class="ms-auto">
                            <!-- زر تحميل الصورة -->
                            <a class="bi bi-download text-info" title="{{ \App\Helpers\translate('download_image') }}" href="{{ asset('storage/' . $image) }}" download></a>

                            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="{{ \App\Helpers\translate('cancel') }}"></button>
                        </div>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded shadow" alt="Image">
                    </div>
                </div>
            </div>
        </div>
    @else
        <span class="badge bg-secondary">{{ \App\Helpers\translate('nothing') }}</span>
    @endif
@endif
