{{-- Metronic app sidebar — Admin guard. Built dynamically from permissions_groups (see PATTERN_DESIGN.md #5) --}}
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
     data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <div class="app-sidebar-logo px-6 d-flex align-items-center justify-content-center" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center app-sidebar-logo-default">
            <img src="{{ asset('site/images/full_mark_dark.png') }}" alt="Full Mark Academy" class="h-50px">
        </a>
        <a href="{{ route('admin.dashboard') }}" class="app-sidebar-logo-minimize">
            <img src="{{ asset('site/images/full_mark_dark.png') }}" alt="Full Mark Academy" class="h-35px">
        </a>

        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate {{ ($sidebarMinimized ?? false) ? 'active' : '' }}"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-double-left fs-2 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
    </div>

    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
             data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto">
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="kt_app_sidebar_menu"
                 data-kt-menu="true" data-kt-menu-expand="false">

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-element-11 fs-2">
                                <span class="path1"></span><span class="path2"></span>
                                <span class="path3"></span><span class="path4"></span>
                            </i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </div>

                @foreach (($sidebar ?? []) as $menu)
                    @if ($menu->children->isEmpty())
                        @if (Route::has($menu->name.'.view') && auth('admin')->user()?->can('admin.'.$menu->name.'.view'))
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs($menu->name.'.*') ? 'active' : '' }}"
                                   href="{{ route($menu->name.'.view') }}">
                                    <span class="menu-icon">
                                        <i class="{{ $menu->icon ?? 'ki-duotone ki-element-11' }} fs-2" style="color: {{ $menu->color }}">
                                            @for ($p = 1; $p <= 10; $p++)
                                                <span class="path{{ $p }}"></span>
                                            @endfor
                                        </i>
                                    </span>
                                    <span class="menu-title">{{ app()->getLocale() === 'ar' ? ($menu->name_ar ?? $menu->name) : ($menu->name_en ?? $menu->name) }}</span>
                                </a>
                            </div>
                        @endif
                    @elseif ($menu->children->contains(fn ($child) => Route::has($child->name.'.view') && auth('admin')->user()?->can('admin.'.$child->name.'.view')))
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">{{ app()->getLocale() === 'ar' ? ($menu->name_ar ?? $menu->name) : ($menu->name_en ?? $menu->name) }}</span>
                            </div>
                        </div>
                        @foreach ($menu->children as $child)
                            @if (Route::has($child->name.'.view') && auth('admin')->user()?->can('admin.'.$child->name.'.view'))
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs($child->name.'.*') ? 'active' : '' }}"
                                       href="{{ route($child->name.'.view') }}">
                                        <span class="menu-icon">
                                            <i class="{{ $child->icon ?? 'ki-duotone ki-element-11' }} fs-2" style="color: {{ $child->color }}">
                                                @for ($p = 1; $p <= 10; $p++)
                                                    <span class="path{{ $p }}"></span>
                                                @endfor
                                            </i>
                                        </span>
                                        @if ($child->name === 'approvals')
                                            <span class="menu-title d-flex align-items-center justify-content-between flex-grow-1">
                                                <span>{{ app()->getLocale() === 'ar' ? ($child->name_ar ?? $child->name) : ($child->name_en ?? $child->name) }}</span>
                                                <span id="sidebar-badge-approvals" class="badge badge-light-danger badge-circle fs-9 ms-2"
                                                      style="{{ $pending_applications_count > 0 ? 'display: inline-block;' : 'display: none;' }}">
                                                    {{ $pending_applications_count }}
                                                </span>
                                            </span>
                                        @else
                                            <span class="menu-title">{{ app()->getLocale() === 'ar' ? ($child->name_ar ?? $child->name) : ($child->name_en ?? $child->name) }}</span>
                                        @endif
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach

            </div>
        </div>
    </div>
</div>
