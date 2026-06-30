{{-- Reusable index-page search filter card. Usage: @include('admin.components.search-filter', ['route' => 'programs.view', 'placeholder' => __('app.search')]) --}}
<div class="card mb-5">
    <div class="card-body py-4">
        <form method="GET" action="{{ route($route) }}" class="d-flex flex-wrap gap-3 align-items-end">
            <div class="flex-grow-1" style="min-width: 220px;">
                <label class="form-label">{{ __('app.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $placeholder ?? __('app.search') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="ki-duotone ki-magnifier fs-3"><span class="path1"></span><span class="path2"></span></i>
                    {{ __('app.search') }}
                </button>
                @if (request('search'))
                    <a href="{{ route($route) }}" class="btn btn-light">{{ __('app.reset') }}</a>
                @endif
            </div>
        </form>
    </div>
</div>
