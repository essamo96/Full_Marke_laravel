<?php

namespace App\Http\Controllers\Admin;

use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\Company;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\PartnerRequest;
use Auth;

class PartnersController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'partners';
        $this->path = 'partners';
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
        $obj = new Partner();
        $info = $obj->getSearch($name, $companies, $emp_id);
        return DataTables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->addColumn('company_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->company ? $row->company->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('partner_name', function ($row) {
            $data['active_menu'] = $this->path;
            $data['id'] = $row->id;
            $data['x'] = 3;
            $data['name'] = $row->translation ? $row->translation->name : '';
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        })
            ->addColumn('image', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 4;
                $data['image'] = $row->image;
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'company_id', 'image','partner_name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        parent::$data['companies'] = Company::all();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(PartnerRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        // رفع الصورة
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('partners', 'public');
        }

        // إنشاء الشريك
        $partner = Partner::create($data);

        // إنشاء الترجمات
        $languages = Language::where('status', 1)->pluck('prefix');
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                PartnerTranslation::create([
                    'partner_id' => $partner->id,
                    'locale'     => $locale,
                    'name'       => $translationData['name'],
                    'address'    => $translationData['address'] ?? null,
                    'details'    => $translationData['details'] ?? null,
                ]);
            }
        }

        Cache::forget('spatie.permission.cache');
        $request->session()->flash('success', __('app.insert_success'));
        return redirect(route($this->path . '.view'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Partner::with('translations')->findOrFail($decryptedId);

        $translations = [];
        foreach ($record->translations as $trans) {
            $translations[$trans->locale] = $trans;
        }

        parent::$data['info'] = $record;
        parent::$data['companies'] = Company::all();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['translations'] = $translations;
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(PartnerRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $partner = Partner::findOrFail($decryptedId);

        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        // تحديث الصورة
        if ($request->hasFile('image')) {
            if ($partner->image && Storage::disk('public')->exists($partner->image)) {
                Storage::disk('public')->delete($partner->image);
            }
            $data['image'] = $request->file('image')->store('partners', 'public');
        } else {
            $data['image'] = $partner->image;
        }

        // تحديث بيانات الشريك
        $partner->update($data);

        // تحديث الترجمات
        $languages = Language::where('status', 1)->pluck('prefix');
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                PartnerTranslation::updateOrCreate(
                    ['partner_id' => $partner->id, 'locale' => $locale],
                    [
                        'name'    => $translationData['name'],
                        'address' => $translationData['address'] ?? null,
                        'details' => $translationData['details'] ?? null,
                    ]
                );
            }
        }

        Cache::forget('spatie.permission.cache');
        $request->session()->flash('success', __('app.update_success'));
        return redirect(route($this->path . '.view'));
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = Partner::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status'  => 'success',
                'message' => $newStatus ? __('app.activation_success') : __('app.disable_success'),
                'type'    => $newStatus ? 'yes' : 'no'
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
            $record = Partner::findOrFail($decryptedId);
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
}
