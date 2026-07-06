<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\NewsTranslation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Admin\NewsRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Storage;

class NewsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'news';
        $this->path = 'news';
    }

    public function getIndex()
    {
        parent::$data['categories'] = Category::all();
        parent::$data['companies'] = Company::all();
        parent::$data['users'] = User::all();

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        $emp_id = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;
        $obj = new News();
        $info = $obj->getSearch($name, $companies, $emp_id);
        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->addColumn('company_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->company ? $row->company->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('category_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $locale = app()->getLocale();
                $column = 'name_' . $locale;
                $data['name'] = $row->category ? $row->category->{$column} : '';
                if ($data['name'] == '') {
                    return "<span class=\"text-danger\">" . __('app.nothing') . "</span>";
                }
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('pub_date', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 4;

                $data['name'] = $row->pub_date ? date('Y-m-d', strtotime($row->pub_date)) : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('title', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->translation ? $row->translation->title : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->editColumn('publish', function ($row) {
                return $row->publish
                    ? '<i class="bi bi-check-circle-fill text-success fs-2"></i>'
                    : '<i class="bi bi-x-circle-fill text-danger fs-2"></i>';
            })
            ->editColumn('main', function ($row) {
                return $row->main
                    ? '<i class="bi bi-check-circle-fill text-success fs-2"></i>'
                    : '<i class="bi bi-x-circle-fill text-danger fs-2"></i>';
            })
            ->addColumn('user_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $column = 'name';
                $data['name'] = $row->user ? $row->user->{$column} : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'title', 'actions', 'company_id', 'user_id', 'main', 'publish', 'pub_date', 'category_id'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = null;
        parent::$data['companies'] = Company::where('status', 1)->get();
        parent::$data['categories'] = Category::where('status', 1)->get();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(NewsRequest $request)
    {
        // البيانات المفلترة
        $data = $request->validated();

        // إضافة معرف الناشر (المستخدم الحالي)
        $data['user_id'] = Auth::guard('admin')->id();

        // معالجة checkboxes
        $data['main'] = $request->has('main') ? 1 : 0;
        $data['publish'] = $request->has('publish') ? 1 : 0;

        // رفع الصورة إن وجدت
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        } else {
            // إذا تم إدخال رابط للصورة يدويًا
            if ($request->filled('image')) {
                $data['image'] = $request->input('image');
            } else {
                $data['image'] = null;
            }
        }

        // إنشاء الخبر
        $news = News::create($data);

        // إضافة الترجمات لكل لغة
        $languages = Language::where('status', 1)->get();
        foreach ($languages as $lang) {
            $translationData = $request->input($lang->prefix);
            if ($translationData) {
                NewsTranslation::create([
                    'news_id' => $news->id,
                    'locale'  => $lang->prefix,
                    'title'   => $translationData['title'] ?? null,
                    'sub'     => $translationData['sub'] ?? null,
                    'descs'   => $translationData['descs'] ?? null,
                ]);
            }
        }

        // مسح الكاش إن أردت
        Cache::forget('spatie.permission.cache');

        // رسالة نجاح
        $request->session()->flash('success', __('app.insert_success'));

        return redirect()->route($this->path . '.view');
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect()->route($this->path . '.view');
        }

        $record = News::with('translations')->findOrFail($decryptedId);

        $translations = [];
        foreach ($record->translations as $trans) {
            $translations[$trans->locale] = $trans;
        }

        parent::$data['info'] = $record;
        parent::$data['companies'] = Company::where('status', 1)->get();
        parent::$data['categories'] = Category::where('status', 1)->get();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['translations'] = $translations;
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(NewsRequest $request, $id)
    {
        // فك التشفير
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect()->route($this->path . '.view');
        }

        // جلب الخبر
        $news = News::findOrFail($decryptedId);

        // البيانات المفلترة
        $data = $request->validated();

        // إضافة معرف الناشر (المستخدم الحالي)
        $data['user_id'] = Auth::guard('admin')->id();

        // معالجة checkboxes
        $data['main'] = $request->has('main') ? 1 : 0;
        $data['publish'] = $request->has('publish') ? 1 : 0;

        // رفع الصورة إن وجدت
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إن وجدت
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }
            $data['image'] = $request->file('image')->store('news', 'public');
        } else {
            // إذا لم يتم رفع صورة جديدة احتفظ بالقديمة
            $data['image'] = $news->image;
        }

        // تحديث البيانات الأساسية
        $news->update($data);

        // تحديث الترجمات لكل لغة
        $languages = Language::where('status', 1)->get();
        foreach ($languages as $lang) {
            $translationData = $request->input($lang->prefix);

            if ($translationData) {
                NewsTranslation::updateOrCreate(
                    [
                        'news_id' => $news->id,
                        'locale' => $lang->prefix,
                    ],
                    [
                        'title' => $translationData['title'] ?? null,
                        'sub'   => $translationData['sub'] ?? null,
                        'descs' => $translationData['descs'] ?? null,
                    ]
                );
            }
        }

        // مسح الكاش إن أردت
        Cache::forget('spatie.permission.cache');

        // رسالة نجاح
        $request->session()->flash('success', __('app.update_success'));

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

        $record = News::findOrFail($decryptedId);

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
            $record = News::findOrFail($decryptedId);
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

    public function upload(Request $request)
{
    if ($request->hasFile('upload')) {
        $file     = $request->file('upload');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('uploads/news', $filename, 'public');

        $url = asset('storage/uploads/news/' . $filename);

        // CKEditor 4 يحتاج json أو script حسب النسخة
        return response()->json([
            "uploaded" => 1,
            "fileName" => $filename,
            "url"      => $url
        ]);
    }

    return response()->json([
        "uploaded" => 0,
        "error" => [ "message" => "لم يتم رفع أي ملف" ]
    ]);
}

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return response()->json([
                    'error' => 'Invalid file type. Only images are allowed.'
                ], 400);
            }

            // Validate file size (max 5MB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json([
                    'error' => 'File size too large. Maximum size is 5MB.'
                ], 400);
            }

            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store file
            $path = $file->storeAs('uploads/tinymce', $filename, 'public');

            // Return URL for TinyMCE
            $url = asset('storage/uploads/tinymce/' . $filename);

            return response()->json([
                'location' => $url
            ]);
        }

        return response()->json([
            'error' => 'No file uploaded'
        ], 400);
    }

}
