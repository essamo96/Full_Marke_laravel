<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Wrapper-->
    <div id="kt_app_sidebar_wrapper" class="app-sidebar-wrapper hover-scroll-y my-5 my-lg-2" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_header" data-kt-scroll-wrappers="#kt_app_sidebar_wrapper" data-kt-scroll-offset="5px">
        <!--begin::Sidebar menu-->
        <div id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false" class="app-sidebar-menu-primary menu menu-column menu-rounded menu-sub-indention menu-state-bullet-primary px-6 mb-5">
            
            <!-- Dashboard Link (Removed as it will be dynamic) -->


            @if(isset($sidebar))
                @foreach ($sidebar as $parent_item)
                    <?php
                    $parentPermission = 'admin.' . $parent_item->name . '.view';
                    $parentRoute = $parent_item->name . '.view';
                    
                    $lang = app()->getLocale();
                    $parentTitle = $parent_item->{'name_' . $lang} ?? $parent_item->name;
                    $parentColor = $parent_item->color ? 'text-' . $parent_item->color : 'text-primary';
                    $parentIcon = ($parent_item->icon ?: 'bi-circle') . ' fs-2 ' . $parentColor;
                    
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
                                    
                                    $childUrl = Route::has($childRoute) ? route($childRoute) : '#';
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
                            $parentUrl = Route::has($parentRoute) ? route($parentRoute) : '#';
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
