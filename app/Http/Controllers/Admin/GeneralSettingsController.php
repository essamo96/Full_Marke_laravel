<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneralSetting;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\GeneralSettingRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class GeneralSettingsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'general_settings';
        $this->path = 'general_settings';
    }

    public function getIndex()
    {
        parent::$data['companies'] = Company::all();

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        $emp_id = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;
        $obj = new GeneralSetting();
        $info = $obj->getSearch($name, $companies, $emp_id);
        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->addColumn('company_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->company ? $row->company->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('site_email', function ($row) {
                $data['active_menu'] = $this->path;
                $data['x'] = 3;
                $data['name'] = $row->site_email;
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('site_phone', function ($row) {
                $data['active_menu'] = $this->path;
                $data['x'] = 3;
                $data['name'] = $row->site_phone;
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('site_address', function ($row) {
                $data['active_menu'] = $this->path;
                $data['x'] = 3;
                $data['name'] = $row->site_address;
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })

            ->rawColumns(['status','actions','company_id','site_email','site_phone','site_address'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        parent::$data['companies'] = Company::all();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(GeneralSettingRequest $request)
    {
        $validated = $request->validated();

        // فصل الحقول الأساسية عن options
        $baseData = [
            'company_id'   => $validated['company_id'],
            'site_email'   => $validated['site_email'],
            'site_phone'   => $validated['site_phone'],
            'site_address' => $validated['site_address'],
            'status'       => $validated['status'] ?? 0,
        ];

        // options: نخزن فيها الباقي (JSON)
        $options = [
            'show_contact_form'       => $validated['show_contact_form'] ?? 0,
            'enable_newsletter'       => $validated['enable_newsletter'] ?? 0,
            'enable_live_chat'        => $validated['enable_live_chat'] ?? 0,
            'show_registration_button'=> $validated['show_registration_button'] ?? 0,
            'homepage_layout'         => $validated['homepage_layout'] ?? 'grid',
            'sections_order'          => $validated['sections_order'] ?? [],
            'welcome_message'         => $validated['welcome_message'] ?? [],
            'close_message'           => $validated['close_message'] ?? [],
        ];

        $baseData['options'] = json_encode($options, JSON_UNESCAPED_UNICODE);

        $record = GeneralSetting::create($baseData);

        if ($record) {
            $request->session()->flash('success', __('app.insert_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.add'))->withInput();
        }
    }


    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = GeneralSetting::findOrFail($decryptedId);
        parent::$data['info'] = $record;
        parent::$data['companies'] = Company::all();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(GeneralSettingRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = GeneralSetting::findOrFail($decryptedId);

        $validated = $request->validated();

        // فصل الحقول الأساسية عن options
        $baseData = [
            'company_id'   => $validated['company_id'],
            'site_email'   => $validated['site_email'],
            'site_phone'   => $validated['site_phone'],
            'site_address' => $validated['site_address'],
            'status'       => $validated['status'] ?? 0,
        ];

        // options: نخزن فيها الباقي (JSON)
        $options = [
            'show_contact_form'       => $validated['show_contact_form'] ?? 0,
            'enable_newsletter'       => $validated['enable_newsletter'] ?? 0,
            'enable_live_chat'        => $validated['enable_live_chat'] ?? 0,
            'show_registration_button'=> $validated['show_registration_button'] ?? 0,
            'homepage_layout'         => $validated['homepage_layout'] ?? 'grid',
            'sections_order'          => $validated['sections_order'] ?? [],
            'welcome_message'         => $validated['welcome_message'] ?? [],
            'close_message'           => $validated['close_message'] ?? [],
        ];

        $baseData['options'] = json_encode($options, JSON_UNESCAPED_UNICODE);

        $update = $record->update($baseData);

        if ($update) {
            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = GeneralSetting::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status' => 'success',
                'message' => $newStatus ? __('app.activation_success') : __('app.disable_success'),
                'type' => $newStatus ? 'yes' : 'no'
            ]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        try {
            $record = GeneralSetting::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
        if ($record->delete()) {
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }

    /**
     * Get categories for a specific company via AJAX
     * Returns id => translation->name for each category
     */
    public function getCompanyCategories($company_id)
    {
        $locale = app()->getLocale();

        $sections = Category::where('company_id', $company_id)
            ->orderBy('sort', 'asc')
            ->with(['translation' => function($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->get()
            ->mapWithKeys(function($category) {
                return [
                    $category->id => $category->translation ? $category->translation->name : ''
                ];
            });

        return response()->json($sections);
    }

        /**
     * جلب الأقسام الخاصة بشركة معينة
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSections(Request $request)
    {
      $company_id = $request->company_id;

        if (!$company_id) {
            return response()->json(['sections' => []]);
        }

        $categories = Category::with('translation')
            ->where('company_id', $company_id)
            ->active()
            ->orderBy('sort', 'asc')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->translation ? $category->translation->name : '---'
                ];
            });

        return response()->json(['sections' => $categories]);
    }
}
