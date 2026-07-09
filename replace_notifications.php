<?php
$content = file_get_contents('resources/views/admin/layout/mainLayouts/header.blade.php');
$start = strpos($content, '<!--begin::Notifications-->');
$end = strpos($content, '<!--end::Notifications-->') + strlen('<!--end::Notifications-->');

if ($start !== false && $end !== false) {
    $newHtml = <<<'HTML'
<!--begin::Notifications-->
<div class="app-navbar-item ms-2 ms-lg-6">
    <!--begin::Menu- wrapper-->
    <div class="btn btn-icon btn-custom btn-color-gray-600 btn-active-color-primary w-35px h-35px w-md-40px h-md-40px position-relative" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
        <i class="ki-outline ki-notification-bing fs-1"></i>
        @if(auth('admin')->check() && auth('admin')->user()->unreadNotifications->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger w-15px h-15px ms-n4 mt-3" id="notification_count_badge">{{ auth('admin')->user()->unreadNotifications->count() }}</span>
        @else
            <span class="position-absolute top-0 start-100 translate-middle badge badge-circle badge-danger w-15px h-15px ms-n4 mt-3 d-none" id="notification_count_badge">0</span>
        @endif
    </div>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true" id="kt_menu_notifications">
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('{{ asset('assets/admin/media/misc/menu-header-bg.jpg') }}')">
            <!--begin::Title-->
            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">@lang('app.notifications')
            <span class="fs-8 opacity-75 ps-3" id="notification_count_text">{{ auth('admin')->check() ? auth('admin')->user()->unreadNotifications->count() : 0 }} @lang('app.new')</span></h3>
            <!--end::Title-->
        </div>
        <!--end::Heading-->
        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade show active" id="kt_topbar_notifications_1" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8" id="notifications_list">
                    @if(auth('admin')->check())
                        @forelse(auth('admin')->user()->unreadNotifications as $notification)
                            <!--begin::Item-->
                            <div class="d-flex flex-stack py-4">
                                <!--begin::Section-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-35px me-4">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-outline ki-abstract-28 fs-2 text-primary"></i>
                                        </span>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Title-->
                                    <div class="mb-0 me-2">
                                        <a href="{{ isset($notification->data['url']) ? $notification->data['url'] : '#' }}" class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ $notification->data['student_name'] ?? 'إشعار جديد' }}</a>
                                        <div class="text-gray-400 fs-7">{{ $notification->data['message'] ?? '' }}</div>
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Section-->
                                <!--begin::Label-->
                                <span class="badge badge-light fs-8">{{ $notification->created_at->diffForHumans() }}</span>
                                <!--end::Label-->
                            </div>
                            <!--end::Item-->
                        @empty
                            <div class="text-center py-4 text-muted" id="no_notifications_msg">لا توجد إشعارات جديدة</div>
                        @endforelse
                    @endif
                </div>
                <!--end::Items-->
                <!--begin::View more-->
                <div class="py-3 text-center border-top">
                    <a href="{{ route('pending_requests.view') }}" class="btn btn-color-gray-600 btn-active-color-primary">عرض الكل
                    <i class="ki-outline ki-arrow-right fs-5"></i></a>
                </div>
                <!--end::View more-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Menu-->
    <!--end::Menu wrapper-->
</div>
<!--end::Notifications-->
HTML;

    $newContent = substr($content, 0, $start) . $newHtml . substr($content, $end);
    file_put_contents('resources/views/admin/layout/mainLayouts/header.blade.php', $newContent);
    echo "Replaced successfully\n";
} else {
    echo "Could not find tags\n";
}
