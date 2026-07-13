<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Wrapper-->
    <div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper hover-scroll-y my-5 my-lg-2" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper" data-kt-scroll-offset="5px">
        <!--begin::Sidebar menu-->
        <div id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary px-6 mb-5">
            
            <!-- Dashboard Link (Removed as it will be dynamic) -->


            @if(isset($sidebar))
                @foreach ($sidebar as $parent_item)
                    <?php
                    if ($parent_item->name == 'dashboard') {
                        $parentPermission = 'admin.' . $parent_item->name . '.view';
                        $parentRoute = 'admin.dashboard';
                    } else {
                        $parentPermission = 'admin.' . $parent_item->name . '.view';
                        $parentRoute = $parent_item->name . '.view';
                    }
                    
                    $lang = app()->getLocale();
                    $parentTitle = $parent_item->{'name_' . $lang} ?? $parent_item->name;
                    $parentColor = $parent_item->color ? 'text-' . $parent_item->color : 'text-primary';
                    $rawIcon = $parent_item->icon ?: 'bi-circle';
                    $baseIconClass = str_starts_with($rawIcon, 'ki-') ? $rawIcon : 'bi ' . $rawIcon;
                    $parentIcon = $baseIconClass . ' fs-2 ' . $parentColor;
                    
                    $parentActive = isset($active_menu) && $active_menu == $parent_item->name;
                    ?>

                    @can($parentPermission)
                        @if ($parent_item->mychild && sizeof($parent_item->mychild) > 0)
                            <x-admin.sidebar-menu 
                                :title="$parentTitle" 
                                :icon="$parentIcon" 
                                :active="$parentActive">
                                
                                @foreach ($parent_item->mychild as $child_item)
                                    <?php 
                                    $childPermission = 'admin.' . $child_item->name . '.view'; 
                                    $childRoute = $child_item->name . '.view'; 
                                    $childTitle = $child_item->{'name_' . $lang} ?? $child_item->name;
                                    $childActive = isset($active_menu) && $active_menu == $child_item->name;
                                    
                                    try {
                                        $childUrl = Route::has($childRoute) ? route($childRoute) : '#';
                                    } catch (\Exception $e) {
                                        \Log::error("Sidebar route generation failed for childRoute {$childRoute}: " . $e->getMessage());
                                        $childUrl = '#';
                                    }

                                    if ($childRoute == 'pending_requests.view') {
                                        $pendingCount = auth('admin')->check() ? auth('admin')->user()->unreadNotifications->where('type', 'App\Notifications\NewStudentRegisteredNotification')->count() : 0;
                                        if($pendingCount > 0) {
                                            $childTitle .= ' <span class="badge badge-danger ms-2" id="sidebar_pending_badge">' . $pendingCount . '</span>';
                                        } else {
                                            $childTitle .= ' <span class="badge badge-danger ms-2 d-none" id="sidebar_pending_badge">0</span>';
                                        }
                                    }
                                    
                                    if ($childRoute == 'approvals.view') {
                                        $approvalsCount = auth('admin')->check() ? auth('admin')->user()->unreadNotifications->where('type', 'App\Notifications\NewPaymentSubmittedNotification')->count() : 0;
                                        if($approvalsCount > 0) {
                                            $childTitle .= ' <span class="badge badge-danger ms-2" id="sidebar_approvals_badge">' . $approvalsCount . '</span>';
                                        } else {
                                            $childTitle .= ' <span class="badge badge-danger ms-2 d-none" id="sidebar_approvals_badge">0</span>';
                                        }
                                    }
                                    ?>
                                    @can($childPermission)
                                        <x-admin.sidebar-item 
                                            :title="$childTitle" 
                                            :url="$childUrl" 
                                            :active="$childActive" />
                                    @endcan
                                @endforeach
                                
                            </x-admin.sidebar-menu>
                        @else
                            <?php 
                            try {
                                $parentUrl = Route::has($parentRoute) ? route($parentRoute) : '#';
                            } catch (\Exception $e) {
                                $parentUrl = '#';
                            }
                            ?>
                            <x-admin.sidebar-item 
                                :title="$parentTitle" 
                                :icon="$parentIcon" 
                                :url="$parentUrl" 
                                :active="$parentActive" />
                        @endif
                    @endcan
                @endforeach
            @endif

        </div>
        <!--end::Sidebar menu-->
    </div>
    <!--end::Wrapper-->
</div>
