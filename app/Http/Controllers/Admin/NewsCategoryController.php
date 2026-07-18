<?php

namespace App\Http\Controllers\Admin;

use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class NewsCategoryController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'news_categories';
    }

    public function getIndex()
    {
        return view('admin.news_categories.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $categories = NewsCategory::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")));

        return DataTables::of($categories)
            ->editColumn('image', function ($row) {
                if ($row->image) {
                    $imagePath = Str::startsWith($row->image, ['http', 'site/']) 
                        ? asset($row->image) 
                        : asset('storage/' . $row->image);
                    return '<div class="symbol symbol-50px symbol-circle me-5">
                            <img src="' . $imagePath . '" alt="image" class="symbol-label">
                        </div>';
                }
                return '-';
            })
            ->addColumn('name', fn ($row) => $row->name_ar)
            ->addColumn('status', fn ($row) => view('admin.news_categories.parts.status', ['category' => $row])->render())
            ->addColumn('actions', fn ($row) => view('admin.news_categories.parts.actions', ['category' => $row])->render())
            ->rawColumns(['image', 'name', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.news_categories.add', self::$data + ['info' => null]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'slug' => 'required|string|max:191|unique:news_categories,slug',
            'image' => 'nullable|image',
        ]);

        $data = $request->only(['name_ar', 'name_en', 'slug']);
        $data['status'] = $request->boolean('status', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news_categories', 'public');
        }

        NewsCategory::create($data);

        return redirect()->route('news_categories.view')->with('success', __('app.insert_success') ?? 'Added successfully');
    }

    public function getEdit($id)
    {
        try {
            $category = NewsCategory::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('news_categories.view')->with('danger', __('app.not_found'));
        }

        return view('admin.news_categories.add', self::$data + ['info' => $category]);
    }

    public function postEdit(Request $request, $id)
    {
        try {
            $category = NewsCategory::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('news_categories.view')->with('danger', __('app.not_found'));
        }

        $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'slug' => 'required|string|max:191|unique:news_categories,slug,'.$category->id,
            'image' => 'nullable|image',
        ]);

        $data = $request->only(['name_ar', 'name_en', 'slug']);
        $data['status'] = $request->boolean('status', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news_categories', 'public');
        }

        $category->update($data);

        return redirect()->route('news_categories.view')->with('success', __('app.update_success') ?? 'Updated successfully');
    }

    public function postStatus(Request $request)
    {
        try {
            $category = NewsCategory::findOrFail(Crypt::decrypt($request->id));
            $category->update(['status' => !$category->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $category = NewsCategory::findOrFail(Crypt::decrypt($request->id));
            $category->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
