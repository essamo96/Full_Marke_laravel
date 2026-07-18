<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class ContactMessageController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'contact_messages';
    }

    public function getIndex()
    {
        return view('admin.contact_messages.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $messages = ContactMessage::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")));

        return DataTables::of($messages)
            ->addColumn('name', fn ($row) => $row->name)
            ->addColumn('email', fn ($row) => $row->email)
            ->addColumn('subject', fn ($row) => $row->subject)
            ->addColumn('is_read', fn ($row) => $row->is_read ? '<span class="badge badge-light-success">مقروءة</span>' : '<span class="badge badge-light-danger">جديدة</span>')
            ->addColumn('actions', fn ($row) => view('admin.contact_messages.parts.actions', ['message_obj' => $row])->render())
            ->rawColumns(['is_read', 'actions'])
            ->toJson();
    }

    public function postDelete(Request $request)
    {
        try {
            $message = ContactMessage::findOrFail(Crypt::decrypt($request->id));
            $message->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
