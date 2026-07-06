<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\ContactRequest;
use Illuminate\Support\Facades\Auth;

class ContactsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'contacts';
        $this->path = 'contacts';
    }

    public function getIndex()
    {
        parent::$data['companies'] = Company::all();

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $records = Contact::get();

        return Datatables::of($records)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->editColumn('contact_type', function ($row) {
                return $row->contact_type == 'government'
                ? \App\Helpers\translate('government')
                : \App\Helpers\translate('person');

            })
            ->addColumn('company_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->company ? $row->company->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->editColumn('message', function ($row) {
                // تحويل علامات الاقتباس فقط لحماية الجافاسكربت
                $message = htmlspecialchars($row->message, ENT_QUOTES, 'UTF-8');

                // زر مع SweetAlert2
                return '<button type="button" class="btn btn-info btn-sm" onclick="Swal.fire({
        title: \'Message\',
        html: \'' . $message . '\',
        icon: \'info\',
        width: 600,
        confirmButtonText: \'Close\'
    });">عرض الرسالة</button>';
            })


            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'company_id', 'message'])
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

    public function postAdd(ContactRequest $request)
    {
        $data = $request->validated();
        if (isset($data['status'])) {
            $data['status'] = $request->input('status') == '1' ? 1 : 0;
        }

        $record = Contact::create($data);

        if ($record) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', \App\Helpers\translate('insert_success'));
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

        $record = Contact::findOrFail($decryptedId);
        parent::$data['info'] = $record;
        parent::$data['companies'] = Company::all();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(ContactRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Contact::findOrFail($decryptedId);

        $validatedData = $request->validated();
        if (isset($validatedData['status'])) {
            $validatedData['status'] = $request->input('status') == '1' ? 1 : 0;
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

        $record = Contact::findOrFail($decryptedId);

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
            $record = Contact::findOrFail($decryptedId);
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
