<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use App\Models\TestimonialTranslation;
use App\Models\Company;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\TestimonialRequest; // هتعمله زي FaqRequest للتحقق
use Auth;

class TestimonialsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'testimonials';
        $this->path = 'testimonials';
    }

    public function getIndex()
    {
        parent::$data['companies'] = Company::all();
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        $emp_id = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;
        $obj = new Testimonial();
        $info = $obj->getSearch($name, $companies, $emp_id);
        return DataTables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = $this->path;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
                        ->addColumn('image', function ($row) {
                if ($row->image) {
                    return '<div class="symbol symbol-50px symbol-circle me-5">
                            <img src="' . asset('storage/' . $row->image) . '" alt="image" class="symbol-label">
                        </div>';
                }
                return '-';
            })
            ->addColumn('company_id', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['x'] = 3;
                $data['name'] = $row->company ? $row->company->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('name', function ($row) {
                $data['x'] = 3;
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                $data['name'] = $row->translation ? $row->translation->name : '';
                return view('admin.' . $this->path . '.parts.general', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'actions', 'company_id', 'name','image'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        parent::$data['info'] = null;
        parent::$data['companies'] = Company::all();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postAdd(TestimonialRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create($data);

        // الترجمات
        $languages = Language::where('status', 1)->pluck('prefix');
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                TestimonialTranslation::create([
                    'testimonials_id' => $testimonial->id,
                    'locale'         => $locale,
                    'name'           => $translationData['name'],
                    'title'          => $translationData['title'] ?? null,
                    'descs'          => $translationData['descs'] ?? null,
                ]);
            }
        }

        Cache::forget('testimonials_cache');
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

        $record = Testimonial::with('translations')->findOrFail($decryptedId);

        $translations = [];
        foreach ($record->translations as $trans) {
            $translations[$trans->locale] = $trans;
        }

        parent::$data['info'] = $record;
        parent::$data['translations'] = $translations;
        parent::$data['companies'] = Company::all();
        parent::$data['languages'] = Language::where('status', 1)->get();
        parent::$data['company_id'] = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->company_id;

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function postEdit(TestimonialRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $testimonial = Testimonial::findOrFail($decryptedId);
        $data = $request->validated();
        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial->update($data);

        // الترجمات
        $languages = Language::where('status', 1)->pluck('prefix');
        foreach ($languages as $locale) {
            $translationData = $request->input($locale, []);
            if (!empty($translationData['name'])) {
                TestimonialTranslation::updateOrCreate(
                    [
                        'testimonials_id' => $testimonial->id,
                        'locale'         => $locale,
                    ],
                    [
                        'name'  => $translationData['name'],
                        'title' => $translationData['title'] ?? null,
                        'descs' => $translationData['descs'] ?? null,
                    ]
                );
            }
        }

        Cache::forget('testimonials_cache');
        $request->session()->flash('success', __('app.edit_success'));
        return redirect(route($this->path . '.view'));
    }

    public function getDelete(Request $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record = Testimonial::find($decryptedId);
        if (!$record) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $record->delete();
        Cache::forget('testimonials_cache');
        $request->session()->flash('success', __('app.delete_success'));
        return redirect(route($this->path . '.view'));
    }
}
