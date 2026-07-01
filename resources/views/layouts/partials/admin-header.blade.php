{{-- Metronic app header — Admin guard --}}
<div id="kt_app_header" class="app-header">
    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">

        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
        </div>

        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ route('admin.dashboard') }}" class="d-lg-none">
                <span class="fs-4 fw-bold text-gray-800">{{ config('app.name', 'Full Mark Academy') }} — Admin</span>
            </a>
        </div>

        <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
            <div class="app-navbar flex-shrink-0 ms-auto">

                {{-- Language switcher --}}
                @php
                    $currentLocale = app()->getLocale();
                    $localeFlags = ['en' => 'united-states.svg', 'ar' => 'palestine.svg'];
                    $localeNames = ['en' => 'English', 'ar' => 'العربية'];
                @endphp
                <div class="app-navbar-item ms-1 ms-md-3">
                    <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <img class="rounded-1 w-20px" src="{{ asset('assets/media/flags/'.($localeFlags[$currentLocale] ?? 'united-states.svg')) }}" alt="{{ $currentLocale }}">
                    </a>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                         data-kt-menu="true">
                        @foreach (array_keys(config('laravellocalization.supportedLocales')) as $locale)
                            <div class="menu-item px-3 my-0">
                                <a href="{{ route('admin.lang', $locale) }}" class="menu-link px-3 py-2 {{ $locale === $currentLocale ? 'active' : '' }}">
                                    <span class="symbol symbol-20px me-4">
                                        <img class="rounded-1" src="{{ asset('assets/media/flags/'.($localeFlags[$locale] ?? 'united-states.svg')) }}" alt="{{ $locale }}">
                                    </span>
                                    {{ $localeNames[$locale] ?? strtoupper($locale) }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Notifications Button & Dropdown --}}
                <div class="app-navbar-item ms-1 ms-md-3">
                    <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px position-relative"
                       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-notification-status fs-2 fs-lg-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                        </i>
                        <span id="unreadNotificationsCount" class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger fs-9"
                              style="{{ $pending_applications_count > 0 ? 'display: inline-block;' : 'display: none;' }} width: 18px; height: 18px; line-height: 18px; padding: 0;">
                            {{ $pending_applications_count }}
                        </span>
                    </a>
                    
                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">
                        <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('{{ asset('assets/media/misc/menu-header-bg.jpg') }}'); background-size: cover;">
                            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                                {{ app()->getLocale() === 'ar' ? 'الإشعارات' : 'Notifications' }}
                                <span id="unreadNotificationsHeaderBadge" class="fs-8 opacity-75 ms-3">{{ $pending_applications_count }} {{ app()->getLocale() === 'ar' ? 'جديد' : 'new' }}</span>
                            </h3>
                        </div>
                        
                        <div class="scroll-y mh-325px my-5 px-8" id="notificationsDropdownList">
                            @forelse($pending_applications as $app)
                                <div class="d-flex align-items-center mb-5 bg-light-warning p-3 rounded">
                                    <div class="symbol symbol-35px me-4">
                                        <span class="symbol-label bg-light-danger">
                                            <i class="ki-duotone ki-profile-user fs-3 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="text-gray-800 fw-bold fs-7">{{ $app->full_name_ar ?: $app->full_name_en }}</span>
                                        <span class="text-muted d-block fs-8">{{ $app->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div id="noNotificationsPlaceholder" class="text-center text-muted py-10">
                                    {{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات جديدة' : 'No new notifications' }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Theme mode (light / dark) --}}
                <div class="app-navbar-item ms-1 ms-md-3">
                    <a href="#" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px"
                       data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-night-day theme-light-show fs-2 fs-lg-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                            <span class="path7"></span><span class="path8"></span><span class="path9"></span>
                            <span class="path10"></span>
                        </i>
                        <i class="ki-duotone ki-moon theme-dark-show fs-2 fs-lg-1">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                    </a>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                         data-kt-menu="true" data-kt-element="theme-mode-menu">
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-duotone ki-night-day fs-2">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        <span class="path4"></span><span class="path5"></span><span class="path6"></span>
                                        <span class="path7"></span><span class="path8"></span><span class="path9"></span>
                                        <span class="path10"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Light</span>
                            </a>
                        </div>
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-duotone ki-moon fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Dark</span>
                            </a>
                        </div>
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                <span class="menu-icon" data-kt-element="icon">
                                    <i class="ki-duotone ki-screen fs-2">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </span>
                                <span class="menu-title">System</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="app-navbar-item ms-1 ms-md-3" id="kt_header_user_menu_toggle">
                    <div class="cursor-pointer symbol symbol-30px symbol-md-40px"
                         data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                         data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                        <img src="{{ asset('assets/media/avatars/300-1.jpg') }}" alt="admin">
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                         data-kt-menu="true">
                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ auth('admin')->user()->name ?? 'Admin' }}
                                    </div>
                                    <span class="fw-semibold text-muted text-hover-primary fs-7">
                                        {{ auth('admin')->user()->email ?? '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                            <a href="{{ route('admin.logout') }}" class="menu-link px-5"
                               onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                Sign Out
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
