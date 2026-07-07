<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\GalleriesRequest;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use DataTables;
use Illuminate\Support\Facades\Storage;

class GalleriesController extends AdminController {

    const INSERT_SUCCESS_MESSAGE = "نجاح، تم الإضافة بتجاح";
    const UPDATE_SUCCESS = "نجاح، تم التعديل بنجاح";
    const DELETE_SUCCESS = "نجاح، تم الحذف بنجاح";
    const EXECUTION_ERROR = "عذراً، حدث خطأ أثناء تنفيذ العملية";
    const NOT_FOUND = "عذراً،لا يمكن العثور على البيانات";
    const ACTIVATION_SUCCESS = "نجاح، تم التفعيل بنجاح";
    const DISABLE_SUCCESS = "نجاح، تم التعطيل بنجاح";

    public function __construct() {
        parent::__construct();
        parent::$data['active_menu'] = 'galleries';
        $this->path = 'galleries';
    }

    public function getIndex() {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request) {
        $model = new Gallery;
        // Eager load company relation
        $info = $model->query()->select('galleries.*'); 
        
        if($request->has('name') && $request->name != ''){
            $info->where('title', 'like', "%{$request->name}%");
        }

        $datatable = Datatables::of($info);

        // Status Column
        $datatable->editColumn('status', function ($row) {
            parent::$data['id'] = $row->id;
            parent::$data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', parent::$data)->render();
        });

        // Image Column using general.blade.php
        $datatable->editColumn('image', function ($row) {
            $data['image'] = $row->image;
            $data['id'] = $row->id;
            $data['x'] = 4;
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        });

        // Company Column
        $datatable;

        // Actions Column
        $datatable->addColumn('actions', function ($row) {
            $data['active_menu'] = $this->path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', $data)->render();
        });

        $datatable->rawColumns(['status', 'image', 'actions']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function getAdd() {
        parent::$data['info'] = new Gallery();
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(GalleriesRequest $request) {
        $save_data = $request->validated();
        
        if ($request->hasFile('image')) {
            $save_data['image'] = $request->file('image')->store('uploads/galleries', 'public');
        }

        $save_data['status'] = $request->has('status') ? 1 : 0;

        $add = Gallery::create($save_data);
        if ($add) {
            $request->session()->flash('success', self::INSERT_SUCCESS_MESSAGE);
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', self::EXECUTION_ERROR);
            return redirect(route($this->path . '.add'))->withInput();
        }
    }

    public function getEdit(Request $request, $id) {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $info = Gallery::findOrFail($id);
        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(GalleriesRequest $request, $id) {
        try {
            $encrypted_id = $id;
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }

        $info = Gallery::findOrFail($id);
        if ($info) {
            $save_data = $request->validated();

            if ($request->hasFile('image')) {
                // Delete old image
                if ($info->image) {
                     Storage::disk('public')->delete($info->image);
                }
                $save_data['image'] = $request->file('image')->store('uploads/galleries', 'public');
            } else {
                unset($save_data['image']);
            }

            $save_data['status'] = $request->has('status') ? 1 : 0;

            $update = $info->update($save_data);
            if ($update) {
                $request->session()->flash('success', self::UPDATE_SUCCESS);
                return redirect(route($this->path . '.view'));
            } else {
                $request->session()->flash('danger', self::EXECUTION_ERROR);
                return redirect(route($this->path . '.edit', ['id' => $encrypted_id]))->withInput();
            }
        } else {
            $request->session()->flash('danger', self::NOT_FOUND);
            return redirect(route($this->path . '.view'));
        }
    }

    public function postStatus(Request $request) {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $info = Gallery::findOrFail($id);

        if ($info) {
            $newStatus = $info->status == 1 ? 0 : 1;
            $update = $info->update(['status' => $newStatus]);
            if ($update) {
                return response()->json([
                            'status' => 'success',
                            'message' => $newStatus ? self::ACTIVATION_SUCCESS : self::DISABLE_SUCCESS,
                            'type' => $newStatus ? 'yes' : 'no'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }

    public function postDelete(Request $request) {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error Decode']);
        }
        $info = Gallery::findOrFail($id);
        if ($info) {
             // Delete image from storage
            if ($info->image) {
                Storage::disk('public')->delete($info->image);
            }
            $delete = $info->delete();
            if ($delete) {
                return response()->json(['status' => 'success', 'message' => self::DELETE_SUCCESS]);
            } else {
                return response()->json(['status' => 'error', 'message' => self::EXECUTION_ERROR]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::NOT_FOUND]);
        }
    }
}
