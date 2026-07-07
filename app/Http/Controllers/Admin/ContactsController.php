<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class ContactsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'contacts';
    }

    public function getIndex()
    {
        return view('admin.contacts.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        
        $obj = new Contact();
        $info = $obj->getSearch($name, $request->get('is_read'));

        return Datatables::of($info)
            ->editColumn('is_read', function ($row) {
                if ($row->is_read) {
                    return '<span class="badge badge-light-success">' . \App\Helpers\translate('read') . '</span>';
                } else {
                    return '<span class="badge badge-light-danger">' . \App\Helpers\translate('unread') . '</span>';
                }
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-';
            })
            ->addColumn('actions', function ($row) {
                $encryptedId = Crypt::encrypt($row->id);
                $viewUrl = route('contacts.show', $encryptedId);
                return '
                    <a href="' . $viewUrl . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="' . \App\Helpers\translate('view') . '">
                        <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </a>
                    <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="deleteItem(\'' . $encryptedId . '\')" title="' . \App\Helpers\translate('delete') . '">
                        <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </button>
                ';
            })
            ->rawColumns(['is_read', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getShow($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route('contacts.view')->with('danger', \App\Helpers\translate('error'));
        }
        parent::$data['contact'] = Contact::findOrFail($id);
        if (!parent::$data['contact']->is_read) {
            parent::$data['contact']->is_read = 1;
            parent::$data['contact']->save();
        }
        return view('admin.contacts.show', parent::$data);
    }

    public function postDelete(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 0]);
        }
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(['status' => 1]);
    }

    public function postStatus(Request $request)
    {
        return response()->json(['status' => 1]);
    }
}
