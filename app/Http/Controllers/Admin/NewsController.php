<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\News;
use App\Models\NewsTranslation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class NewsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'news';
    }

    public function getIndex()
    {
        return view('admin.news.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        
        $obj = new News();
        $info = $obj->getSearch($name, $request->get('status'));

        return Datatables::of($info)
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
            ->addColumn('title', function ($row) {
                return $row->translation ? $row->translation->title : '-';
            })
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = 'news';
                return view('admin.' . $data['active_menu'] . '.parts.status', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['id'] = $row->id;
                $data['active_menu'] = 'news';
                return view('admin.' . $data['active_menu'] . '.parts.actions', $data)->render();
            })
            ->rawColumns(['image', 'status', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        return view('admin.news.add', parent::$data);
    }

    public function postAdd(NewsRequest $request)
    {
        $status = $request->get('status') ? 1 : 0;

        $news = News::create([
            'image' => $request->get('image'),
            'status' => $status,
        ]);

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            NewsTranslation::create([
                'news_id' => $news->id,
                'locale' => $locale,
                'title' => $request->get("title_{$locale}"),
                'description' => $request->get("description_{$locale}"),
            ]);
        }

        return redirect()->route('news.view')->with('success', \App\Helpers\translate('added_successfully'));
    }

    public function getEdit($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route('news.view')->with('danger', \App\Helpers\translate('error'));
        }
        parent::$data['info'] = News::with('translations')->findOrFail($id);
        return view('admin.news.add', parent::$data);
    }

    public function postEdit(NewsRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route('news.view')->with('danger', \App\Helpers\translate('error'));
        }
        $news = News::findOrFail($id);
        $news->status = $request->get('status') ? 1 : 0;
        $news->image = $request->filled('image') ? $request->get('image') : $news->image;
        $news->save();

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            NewsTranslation::updateOrCreate(
                ['news_id' => $news->id, 'locale' => $locale],
                [
                    'title' => $request->get("title_{$locale}"),
                    'description' => $request->get("description_{$locale}"),
                ]
            );
        }

        return redirect()->route('news.view')->with('success', \App\Helpers\translate('edited_successfully'));
    }

    public function postStatus(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 0]);
        }
        $news = News::findOrFail($id);
        $news->status = $request->status;
        $news->save();
        return response()->json(['status' => 1]);
    }

    public function postDelete(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 0]);
        }
        $news = News::findOrFail($id);
        $news->delete();
        return response()->json(['status' => 1]);
    }
}
