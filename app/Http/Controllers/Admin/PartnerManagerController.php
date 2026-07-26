<?php

namespace App\Http\Controllers\Admin;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class PartnerManagerController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'partners';
    }

    public function getIndex()
    {
        return view('admin.partners_manager.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $partners = Partner::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")));

        return DataTables::of($partners)
            ->editColumn('logo', function ($row) {
                if ($row->logo) {
                    $imagePath = Str::startsWith($row->logo, ['http', 'site/']) 
                        ? asset($row->logo) 
                        : asset('storage/' . $row->logo);
                    return '<div class="symbol symbol-50px symbol-circle me-5">
                            <img src="' . $imagePath . '" alt="image" class="symbol-label">
                        </div>';
                }
                return '-';
            })
            ->addColumn('name', fn ($row) => $row->name_ar)
            ->addColumn('status', fn ($row) => view('admin.partners_manager.parts.status', ['partner' => $row])->render())
            ->addColumn('actions', fn ($row) => view('admin.partners_manager.parts.actions', ['partner' => $row])->render())
            ->rawColumns(['logo', 'name', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.partners_manager.add', self::$data + ['info' => null]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'logo' => 'nullable|string|max:255',
            'link' => 'nullable|url',
        ]);

        $data = $request->only(['name_ar', 'name_en', 'link', 'logo']);
        $data['status'] = $request->boolean('status', true);

        Partner::create($data);

        return redirect()->route('partners.view')->with('success', __('app.insert_success') ?? 'Added successfully');
    }

    public function getEdit($id)
    {
        try {
            $partner = Partner::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('partners.view')->with('danger', __('app.not_found'));
        }

        return view('admin.partners_manager.add', self::$data + ['info' => $partner]);
    }

    public function postEdit(Request $request, $id)
    {
        try {
            $partner = Partner::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('partners.view')->with('danger', __('app.not_found'));
        }

        $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'logo' => 'nullable|string|max:255',
            'link' => 'nullable|url',
        ]);

        $data = $request->only(['name_ar', 'name_en', 'link', 'logo']);
        $data['status'] = $request->boolean('status', true);

        $partner->update($data);

        return redirect()->route('partners.view')->with('success', __('app.update_success') ?? 'Updated successfully');
    }

    public function postStatus(Request $request)
    {
        try {
            $partner = Partner::findOrFail(Crypt::decrypt($request->id));
            $partner->update(['status' => !$partner->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $partner = Partner::findOrFail(Crypt::decrypt($request->id));
            $partner->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
