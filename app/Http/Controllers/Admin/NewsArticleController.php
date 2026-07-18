<?php

namespace App\Http\Controllers\Admin;

use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class NewsArticleController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'news_articles';
    }

    public function getIndex()
    {
        return view('admin.news_articles.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $articles = NewsArticle::query()->with(['category', 'admin'])
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")));

        return DataTables::of($articles)
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
            ->addColumn('title', fn ($row) => $row->title_ar)
            ->addColumn('category', fn ($row) => $row->category ? $row->category->name_ar : '-')
            ->addColumn('status', fn ($row) => view('admin.news_articles.parts.status', ['article' => $row])->render())
            ->addColumn('actions', fn ($row) => view('admin.news_articles.parts.actions', ['article' => $row])->render())
            ->rawColumns(['image', 'title', 'category', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.news_articles.add', self::$data + [
            'info' => null,
            'categories' => NewsCategory::where('status', 1)->get()
        ]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'category_id' => 'required|integer|exists:news_categories,id',
            'image' => 'nullable|image',
            'content_ar' => 'nullable|string',
            'content_en' => 'nullable|string',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'category_id', 'content_ar', 'content_en']);
        $data['status'] = $request->boolean('status', true);
        $data['admin_id'] = auth('admin')->id();
        $data['published_at'] = now();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news_articles', 'public');
        }

        NewsArticle::create($data);

        return redirect()->route('news_articles.view')->with('success', __('app.insert_success') ?? 'Added successfully');
    }

    public function getEdit($id)
    {
        try {
            $article = NewsArticle::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('news_articles.view')->with('danger', __('app.not_found'));
        }

        return view('admin.news_articles.add', self::$data + [
            'info' => $article,
            'categories' => NewsCategory::where('status', 1)->get()
        ]);
    }

    public function postEdit(Request $request, $id)
    {
        try {
            $article = NewsArticle::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('news_articles.view')->with('danger', __('app.not_found'));
        }

        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'category_id' => 'required|integer|exists:news_categories,id',
            'image' => 'nullable|image',
            'content_ar' => 'nullable|string',
            'content_en' => 'nullable|string',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'category_id', 'content_ar', 'content_en']);
        $data['status'] = $request->boolean('status', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news_articles', 'public');
        }

        $article->update($data);

        return redirect()->route('news_articles.view')->with('success', __('app.update_success') ?? 'Updated successfully');
    }

    public function postStatus(Request $request)
    {
        try {
            $article = NewsArticle::findOrFail(Crypt::decrypt($request->id));
            $article->update(['status' => !$article->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $article = NewsArticle::findOrFail(Crypt::decrypt($request->id));
            $article->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
