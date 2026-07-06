<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class DashboardController extends AdminController {

    public function __construct() {
        parent::__construct();
        parent::$data['active_menu'] = 'dashboard';
    }

    ///////////////////////////////
    public function getIndex() {
        // dd(self::$data['current_route']);
        return view('admin.dashboard.view', parent::$data);
    }

public function getLang($lang)
{
    if (Auth::guard('admin')->check()) {
        $key = 'locale_' . Auth::guard('admin')->id();
        Session::put($key, $lang);
    } else {
        Session::put('locale', $lang);
    }

    return redirect()->back();
}

}
