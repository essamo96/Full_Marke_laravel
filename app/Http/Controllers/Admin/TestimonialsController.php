<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use App\Models\TestimonialTranslation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class TestimonialsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'testimonials';
    }

    public function getIndex()
    {
        return view('admin.testimonials.view', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';

        $obj = new Testimonial();
        $info = $obj->getSearch($name);

        if ($request->filled('status')) {
            $info->where('testimonials.status', $request->get('status'));
        }

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
            ->addColumn('name', function ($row) {
                return $row->translation ? $row->translation->name : '-';
            })
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                $data['active_menu'] = 'testimonials';
                return view('admin.' . $data['active_menu'] . '.parts.status', $data)->render();
            })
            ->addColumn('actions', function ($row) {
                $data['id'] = $row->id;
                $data['active_menu'] = 'testimonials';
                return view('admin.' . $data['active_menu'] . '.parts.actions', $data)->render();
            })
            ->rawColumns(['image', 'status', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function getAdd()
    {
        return view('admin.testimonials.add', parent::$data);
    }

    public function postAdd(TestimonialRequest $request)
    {
        $status = $request->get('status') ? 1 : 0;

        $testimonial = Testimonial::create([
            'image' => $request->get('image'),
            'status' => $status,
            'display_order' => $request->get('display_order', 0)
        ]);

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            TestimonialTranslation::create([
                'testimonial_id' => $testimonial->id,
                'locale' => $locale,
                'name' => $request->get("name_{$locale}"),
                'position' => $request->get("position_{$locale}"),
                'message' => $request->get("message_{$locale}"),
            ]);
        }

        return redirect()->route('testimonials.view')->with('success', __('app.insert_success'));
    }

    public function getEdit($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route('testimonials.view')->with('danger', \App\Helpers\translate('error'));
        }
        parent::$data['info'] = Testimonial::with('translations')->findOrFail($id);
        return view('admin.testimonials.add', parent::$data);
    }

    public function postEdit(TestimonialRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route('testimonials.view')->with('danger', \App\Helpers\translate('error'));
        }
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->status = $request->get('status') ? 1 : 0;
        $testimonial->display_order = $request->get('display_order', $testimonial->display_order);

        if ($request->filled('image')) {
            $testimonial->image = $request->get('image');
        }
        $testimonial->save();

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            TestimonialTranslation::updateOrCreate(
                ['testimonial_id' => $testimonial->id, 'locale' => $locale],
                [
                    'name' => $request->get("name_{$locale}"),
                    'position' => $request->get("position_{$locale}"),
                    'message' => $request->get("message_{$locale}"),
                ]
            );
        }

        return redirect()->route('testimonials.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 0]);
        }
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->status = $request->status;
        $testimonial->save();
        return response()->json(['status' => 1]);
    }

    public function postDelete(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 0]);
        }
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();
        return response()->json(['status' => 1]);
    }
}
