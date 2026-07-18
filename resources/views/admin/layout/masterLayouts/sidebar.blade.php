<div
    id="kt_app_sidebar"
    class="app-sidebar flex-column"
    data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="225px"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle"
>
    <!-- Sidebar Logo -->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ url('/') }}">
            <!-- اسم الشركة -->
            {{-- <span
                id="app_sidebar_logo_text"
                class="text-warning fw-bold"
                style="font-family: 'Inter', sans-serif !important;"
            >
                {{ \App\Helpers\translate('app.company_name') }}
            </span> --}}

            <!-- صورة الشعار -->
            <img
                src="{{ asset('yabous_logo.png') }}"
                alt="Logo"
                class="h-50px app-sidebar-logo-default fs-2"
            />
            <img
                src="{{ asset('yabous_logo.png') }}"
                alt="Logo"
                class="h-50px app-sidebar-logo-minimize fs-2"
            />
        </a>

        <!-- زر إخفاء/إظهار القائمة -->
        <div
            id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true"
            data-kt-toggle-state="active"
            data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize"
        >
            <i class="ki-duotone ki-double-left fs-2 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div
            id="kt_app_sidebar_menu_wrapper"
            class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
            data-kt-scroll="true"
            data-kt-scroll-activate="true"
            data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu"
            data-kt-scroll-offset="5px"
            data-kt-scroll-save-state="true"
        >
            <div
                id="#kt_app_sidebar_menu"
                class="menu menu-column menu-rounded menu-sub-indention px-3"
                data-kt-menu="true"
                data-kt-menu-expand="false"
            >
                @foreach ($sidebar as $item)
                    @php
                        $namep = 'admin.' . ($item->name ?? '') . '.view';
                    @endphp

                    @can($namep)
                        @if (!empty($item->mychild) && count($item->mychild) > 0)
                            @include('admin.components.sidebar-item-with-children', [
                                'item' => $item,
                                'active_menu' => $active_menu ?? '',
                            ])
                        @else
                            @include('admin.components.sidebar-item-single', [
                                'item' => $item,
                                'active_menu' => $active_menu ?? '',
                            ])
                        @endif
                    @endcan
                @endforeach

                <!-- Static Links added for new features -->
                <div class="menu-item">
                    <a class="menu-link {{ (request()->routeIs('educational_materials.*')) ? 'active' : '' }}" href="{{ route('educational_materials.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-book fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </span>
                        <span class="menu-title">المواد التعليمية (الهرمية)</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ (request()->routeIs('financial_reports.*')) ? 'active' : '' }}" href="{{ route('financial_reports.view') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-dollar fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                        <span class="menu-title">الإدارة المالية</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <a
            href="#"
            class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100"
            data-bs-toggle="tooltip"
            data-bs-trigger="hover"
            data-bs-dismiss-="click"
            title="200+ in-house components and 3rd-party plugins"
        >
            <span class="btn-label">Support & Contact</span>
            <i class="ki-duotone ki-document btn-icon fs-2 m-0">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </a>
    </div>
</div>
