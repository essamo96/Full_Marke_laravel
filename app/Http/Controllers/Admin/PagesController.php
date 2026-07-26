<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Admin\PageRequest;
use App\Models\PagesTranslations;
use Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Encryption\DecryptException;

class PagesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'pages';
        $this->path = 'pages';
    }

    public function getIndex()
    {
        parent::$data['companies'] = [];

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        
        $obj = new Page();
        $info = $obj->getSearch($name, $companies, 0);

        if ($request->filled('status')) {
            $info->where('pages.status', $request->get('status'));
        }

        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            
            ->addColumn('title', function ($row) {
            $data['active_menu'] = $this->path;
            $data['id'] = $row->id;
            $data['x'] = 3;
            $data['name'] = $row->translation ? $row->translation->title : '';
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'title'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = NULL;
        parent::$data['companies'] = [];
        
        parent::$data['languages'] = collect([(object)['prefix'=>'ar','name'=>'العربية'],(object)['prefix'=>'en','name'=>'English']]);

        return view('admin.' . $this->path . '.add', parent::$data);
    }


    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Page::with('translations')->findOrFail($decryptedId); // جلب الترجمات مع الصفحة

        // تجهيز المصفوفة لتسهيل عرض الترجمات
        $translations = [];
        foreach ($record->translations as $trans) {
            $translations[$trans->locale] = $trans;
        }

        parent::$data['info'] = $record;
        parent::$data['companies'] = [];
        
        parent::$data['languages'] = collect([(object)['prefix'=>'ar','name'=>'العربية'],(object)['prefix'=>'en','name'=>'English']]);
        parent::$data['translations'] = $translations;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

public function postEdit(PageRequest $request, $id)
{
    try {
        $decryptedId = Crypt::decrypt($id);
    } catch (\Exception $e) {
        $request->session()->flash('danger', __('app.not_found'));
        return redirect()->route($this->path . '.view');
    }

    $page = Page::findOrFail($decryptedId);

    $data['status'] = $request->has('status') ? 1 : 0;
    $data['image'] = $request->filled('image') ? $request->get('image') : $page->image;
    $data['video'] = $request->filled('video') ? $request->get('video') : $page->video;

    // تحديث البيانات الأساسية
    $page->update([
        'slug'       => $request->slug,
        'tags'       => $request->tags,
        'status'     => $data['status'],
        'image'      => $data['image'],
        'video'      => $data['video']]);

    // ===== translations =====
    $languages = ['ar', 'en'];

    foreach ($languages as $locale) {
        $translationData = $request->$locale ?? null;
        if ($translationData && (isset($translationData['title']) || isset($translationData['details']))) {
            PagesTranslations::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'locale'  => $locale],
                [
                    'title'   => $translationData['title'] ?? null,
                    'details' => $translationData['details'] ?? null]
            );
        }
    }

    Cache::forget('spatie.permission.cache');
    $request->session()->flash('success', __('app.update_success'));

    return redirect()->route($this->path . '.view');
}

public function postAdd(PageRequest $request)
{
    $data['status'] = $request->has('status') ? 1 : 0;
    $data['image'] = $request->get('image');
    $data['video'] = $request->get('video');

    // إنشاء الصفحة الأساسية
    $page = Page::create([
        'slug'       => $request->slug,
        'tags'       => $request->tags,
        'status'     => $data['status'],
        'image'      => $data['image'],
        'video'      => $data['video']]);

    // ===== translations =====
    $languages = ['ar', 'en'];

    foreach ($languages as $locale) {
        if ($request->filled("$locale.title") || $request->filled("$locale.details")) {
            PagesTranslations::create([
                'page_id' => $page->id,
                'locale'  => $locale,
                'title'   => $request->$locale['title'] ?? null,
                'details' => $request->$locale['details'] ?? null]);
        }
    }

    Cache::forget('spatie.permission.cache');
    $request->session()->flash('success', __('app.insert_success'));

    return redirect()->route($this->path . '.view');
}

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = Page::findOrFail($decryptedId);

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
            $record = Page::findOrFail($decryptedId);
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


