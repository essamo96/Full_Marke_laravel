<?php

namespace App\Http\Controllers\Admin;

use App\Models\StaticPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class StaticPageController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'static_pages';
    }

    public function getIndex()
    {
        return view('admin.static_pages.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $pages = StaticPage::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")));

        return DataTables::of($pages)
            ->addColumn('title', fn ($row) => $row->title_ar)
            ->addColumn('slug', fn ($row) => $row->slug)
            ->addColumn('status', fn ($row) => view('admin.static_pages.parts.status', ['page' => $row])->render())
            ->addColumn('actions', fn ($row) => view('admin.static_pages.parts.actions', ['page' => $row])->render())
            ->rawColumns(['title', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.static_pages.add', self::$data + ['info' => null]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'slug' => 'required|string|max:191|unique:static_pages,slug',
            'content_ar' => 'nullable|string',
            'content_en' => 'nullable|string',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'slug', 'content_ar', 'content_en']);
        $data['status'] = $request->boolean('status', true);

        StaticPage::create($data);

        return redirect()->route('static_pages.view')->with('success', __('app.insert_success') ?? 'Added successfully');
    }

    public function getEdit($id)
    {
        try {
            $page = StaticPage::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('static_pages.view')->with('danger', __('app.not_found'));
        }

        return view('admin.static_pages.add', self::$data + ['info' => $page]);
    }

    public function postEdit(Request $request, $id)
    {
        try {
            $page = StaticPage::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('static_pages.view')->with('danger', __('app.not_found'));
        }

        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'slug' => 'required|string|max:191|unique:static_pages,slug,'.$page->id,
            'content_ar' => 'nullable|string',
            'content_en' => 'nullable|string',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'slug', 'content_ar', 'content_en']);
        $data['status'] = $request->boolean('status', true);

        $page->update($data);

        return redirect()->route('static_pages.view')->with('success', __('app.update_success') ?? 'Updated successfully');
    }

    public function postStatus(Request $request)
    {
        try {
            $page = StaticPage::findOrFail(Crypt::decrypt($request->id));
            $page->update(['status' => !$page->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $page = StaticPage::findOrFail(Crypt::decrypt($request->id));
            $page->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
