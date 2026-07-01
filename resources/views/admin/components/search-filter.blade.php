{{--
    Reusable index-page search filter card.
    Plain pages:    @include('admin.components.search-filter', ['route' => 'programs.view', 'placeholder' => __('app.search')])
    DataTables pages (live AJAX filtering, no page reload): add 'datatable' => true
--}}
<div class="card mb-5">
    <div class="card-body py-4">
        <form method="GET" action="{{ route($route) }}" class="d-flex flex-wrap gap-3 align-items-end"
              @if($datatable ?? false) onsubmit="return false;" @endif>
            <div class="flex-grow-1" style="min-width: 220px;">
                <label class="form-label">{{ __('app.search') }}</label>
                <input type="text" id="search-filter-input" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $placeholder ?? __('app.search') }}">
            </div>
            @unless($datatable ?? false)
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-duotone ki-magnifier fs-3"><span class="path1"></span><span class="path2"></span></i>
                        {{ __('app.search') }}
                    </button>
                    @if (request('search'))
                        <a href="{{ route($route) }}" class="btn btn-light">{{ __('app.reset') }}</a>
                    @endif
                </div>
            @endunless
        </form>
    </div>
</div>
