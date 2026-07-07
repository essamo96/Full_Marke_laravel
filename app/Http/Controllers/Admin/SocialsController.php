<?php

namespace App\Http\Controllers\Admin;

use App\Models\Social;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\SocialRequest;
use Auth;

class SocialsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'socials';
        $this->path = 'socials';
    }

    public function getIndex()
    {
        parent::$data['companies'] = [];

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('generalSearch') ?? '';
        $status = $request->get('status');

        $query = Social::query();
        if ($name) {
            $query->where('name', 'LIKE', "%{$name}%");
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return Datatables::of($query)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })


            ->addColumn('image', function ($row) {
                if ($row->image) {
                    $imgUrl = str_starts_with($row->image, 'assets/') ? asset($row->image) : asset('storage/' . $row->image);
                    return '<img src="' . $imgUrl . '" alt="" class="w-50px rounded-1">';
                }
                return '<img src="' . asset('assets/media/svg/avatars/blank.svg') . '" alt="" class="w-50px rounded-1">';
            })
            ->addColumn('name', function ($row) {
                $locale = app()->getLocale();
                return $locale === 'ar' ? $row->name_ar : $row->name_en;
            })
            ->addColumn('icon', function ($row) {
                return '<i class="' . $row->icon . ' fs-2"></i>';
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'image', 'icon', 'name'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        parent::$data['companies'] = [];
        

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(SocialRequest $request)
    {
        if (isset($request->socials) && is_array($request->socials)) {
            foreach ($request->socials as $index => $item) {
                $data = [
                    'name_ar' => $item['name_ar'],
                    'name_en' => $item['name_en'],
                    'link' => $item['link'],
                    'icon' => $item['icon'],
                    'status' => isset($item['status']) ? 1 : 0];

                if ($request->hasFile("socials.$index.image")) {
                    $file = $request->file("socials.$index.image");
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/socials', $filename);
                    $data['image'] = 'socials/' . $filename;
                } elseif (!empty($item['preset_logo'])) {
                    $data['image'] = 'assets/admin/media/svg/social-logos/' . $item['preset_logo'];
                }

                Social::create($data);
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

        $record = Social::findOrFail($decryptedId);
        parent::$data['info'] = $record;
        parent::$data['companies'] = [];
        

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(SocialRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Social::findOrFail($decryptedId);

        $validatedData = [
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'link' => $request->link,
            'icon' => $request->icon,
            'status' => $request->has('status') ? 1 : 0];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/socials', $filename);
            $validatedData['image'] = 'socials/' . $filename;
        } elseif ($request->filled('preset_logo')) {
            $validatedData['image'] = 'assets/admin/media/svg/social-logos/' . $request->input('preset_logo');
        }

        $update = $record->update($validatedData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
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

        $record = Social::findOrFail($decryptedId);

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
            $record = Social::findOrFail($decryptedId);
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
