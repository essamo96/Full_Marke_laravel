<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'settings';
        $this->path = 'settings';
    }

    public function getIndex()
    {
        $settings = Setting::pluck('value', 'key');

        return view('admin.settings.view', self::$data + ['settings' => $settings]);
    }

    public function postUpdate(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:191',
            'site_email' => 'nullable|email|max:191',
            'site_phone' => 'nullable|string|max:50',
            'site_address' => 'nullable|string|max:500',
        ]);

        collect($request->except('_token'))->each(function ($value, $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'general']);
        });

        return redirect()->route('settings.view')->with('success', __('app.update_success'));
    }
}
