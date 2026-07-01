<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\Setting;
use App\Models\PermissionsGroup;
use App\Models\Contact;
use Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/* * **************************** */

class AdminController extends BaseController {

    public static $data = [];

    use AuthorizesRequests,
        DispatchesJobs,
        ValidatesRequests;

public function __construct() {
    $permission_group = new PermissionsGroup();
    self::$data['sidebar'] = $permission_group->getAllParentPermissionGroup();
    self::$data['pending_applications_count'] = \App\Models\Application::where('status', 'new')->count();
    self::$data['pending_applications'] = \App\Models\Application::where('status', 'new')->latest()->take(5)->get();

    // الحصول على اسم الراوت الحالي
    $route_name = Route::currentRouteName();
    $route_data = explode('.', $route_name);
    $current_route = $route_data[0] ?? '';

    // قيمة افتراضية في حال عدم وجود تطابق
    $init_obj = new \stdClass();
    $init_obj->name = '';
    $init_obj->name_ar = '';
    $init_obj->name_en = '';
    $init_obj->parent_id = '';
    self::$data['current_route'] = $init_obj;

    // البحث عن تطابق في القائمة الجانبية (الأبناء أو الأبوين)
    foreach (self::$data['sidebar'] as $menu_item) {
        // تحقق من الأب نفسه
        if ($current_route === $menu_item->name) {
            self::$data['current_route'] = $menu_item;
            break;  // وجدنا التطابق نوقف البحث
        }

        // تحقق من الأبناء إن وجدوا (mychild قد تكون مصفوفة أو empty)
        if (!empty($menu_item->children)) {
            foreach ($menu_item->children as $child_item) {
                if ($current_route === $child_item->name) {
                    self::$data['current_route'] = $child_item;
                    break 2; // خروج من الحلقات كلها
                }
            }
        }
    }
}

}
