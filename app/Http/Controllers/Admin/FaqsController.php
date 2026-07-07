<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\FaqRequest;
use Illuminate\Support\Facades\Auth;

class FaqsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'faqs';
        $this->path = 'faqs';
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
        
        $obj = new Faq();
        $info = $obj->getSearch($name, $companies, 0);

        if ($request->has('status') && $request->get('status') !== null) {
            $info->where('faqs.status', $request->get('status'));
        }

        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            
            ->addColumn('question', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->translation ? $row->translation->question : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'question'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = null;
        parent::$data['companies'] = [];
        parent::$data['languages'] = collect([(object)['prefix'=>'ar','name'=>'العربية'],(object)['prefix'=>'en','name'=>'English']]);
        

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(FaqRequest $request)
    {
        $status = $request->has('status') ? 1 : 0;
        $sort = $request->input('sort', 0);
        $link = $request->input('link');
        $icon = $request->input('icon');

        $ar_faqs = $request->input('ar_faqs', []);
        $en_faqs = $request->input('en_faqs', []);
        
        $count = max(is_array($ar_faqs) ? count($ar_faqs) : 0, is_array($en_faqs) ? count($en_faqs) : 0);

        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                // إنشاء FAQ
                $faq = Faq::create([
                    'status' => $status,
                    'sort' => $sort,
                    'link' => $link,
                    'icon' => $icon,
                ]);

                // الترجمات
                $languages = ['ar', 'en'];
                foreach ($languages as $locale) {
                    $translationData = $request->input("{$locale}_faqs.{$i}");
                    $commonTrans = $request->input($locale, []);
                    if ($translationData && (!empty($translationData['question']) || !empty($translationData['answer']))) {
                        FaqTranslation::create([
                            'faq_id'      => $faq->id,
                            'locale'      => $locale,
                            'question'    => $translationData['question'] ?? '',
                            'answer'      => $translationData['answer'] ?? '',
                            'title'       => $commonTrans['title'] ?? null,
                            'description' => $commonTrans['description'] ?? null
                        ]);
                    }
                }
            }
        } else {
            // Fallback for single insertion (just in case)
            $faq = Faq::create(['status' => $status, 'sort' => $sort, 'link' => $link, 'icon' => $icon]);
            foreach (['ar', 'en'] as $locale) {
                $translationData = $request->input($locale, []);
                if (!empty($translationData['question']) || !empty($translationData['answer'])) {
                    FaqTranslation::create([
                        'faq_id'      => $faq->id,
                        'locale'      => $locale,
                        'question'    => $translationData['question'] ?? '',
                        'answer'      => $translationData['answer'] ?? '',
                        'title'       => $translationData['title'] ?? null,
                        'description' => $translationData['description'] ?? null
                    ]);
                }
            }
        }

        Cache::forget('faqs_cache');
        $request->session()->flash('success', __('app.insert_success'));
        return redirect(route($this->path . '.view'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Faq::with('translations')->findOrFail($decryptedId);

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

    public function postEdit(FaqRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $faq = Faq::findOrFail($decryptedId);

        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        // تحديث FAQ
        $faq->update($data);

        // تحديث الترجمات
        $languages = ['ar', 'en'];
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['question']) || !empty($translationData['answer'])) {
                FaqTranslation::updateOrCreate(
                    ['faq_id' => $faq->id, 'locale' => $locale],
                    [
                        'question'    => $translationData['question'] ?? '',
                        'answer'      => $translationData['answer'] ?? '',
                        'title'       => $translationData['title'] ?? null,
                        'description' => $translationData['description'] ?? null]
                );
            }
        }

        Cache::forget('faqs_cache');
        $request->session()->flash('success', __('app.update_success'));
        return redirect(route($this->path . '.view'));
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }

        $record = Faq::findOrFail($decryptedId);

        $newStatus = $record->status == 1 ? 0 : 1;
        $update = $record->update(['status' => $newStatus]);

        if ($update) {
            Cache::forget('faqs_cache');
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
            $record = Faq::findOrFail($decryptedId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }

        if ($record->delete()) {
            Cache::forget('faqs_cache');
            return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
    }
}


