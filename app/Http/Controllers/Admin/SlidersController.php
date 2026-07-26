<?php

namespace App\Http\Controllers\Admin;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\SliderRequest;
use Auth;

class SlidersController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'sliders';
        $this->path = 'sliders';
    }

    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('generalSearch') ?? '';
        $status = $request->get('status');

        $query = Slider::query();
        if ($name) {
            $query->where('title_ar', 'LIKE', "%{$name}%")->orWhere('title_en', 'LIKE', "%{$name}%");
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
                    return '<img src="' . asset('storage/' . $row->image) . '" alt="" class="w-50px rounded-1">';
                }
                return '<img src="' . asset('assets/media/svg/avatars/blank.svg') . '" alt="" class="w-50px rounded-1">';
            })
            ->addColumn('title', function ($row) {
                $locale = app()->getLocale();
                return $locale === 'ar' ? $row->title_ar : $row->title_en;
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'image', 'title'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(SliderRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        Slider::create($data);

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

        $record = Slider::findOrFail($decryptedId);
        parent::$data['info'] = $record;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(SliderRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Slider::findOrFail($decryptedId);

        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['image'] = $request->filled('image') ? $request->get('image') : $record->image;
        $data['video1'] = $request->filled('video1') ? $request->get('video1') : $record->video1;
        $data['video2'] = $request->filled('video2') ? $request->get('video2') : $record->video2;

        $update = $record->update($data);

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

        $record = Slider::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
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
            $record = Slider::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
        if ($record->delete()) {
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }
}
