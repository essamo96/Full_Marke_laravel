<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaperEdition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class PaperEditionController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'paper_editions';
    }

    public function getIndex()
    {
        return view('admin.paper_editions.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('search_value');

        $editions = PaperEdition::query()
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")));

        return DataTables::of($editions)
            ->editColumn('cover_image', function ($row) {
                if ($row->cover_image) {
                    $imagePath = Str::startsWith($row->cover_image, ['http', 'site/']) 
                        ? asset($row->cover_image) 
                        : asset('storage/' . $row->cover_image);
                    return '<div class="symbol symbol-50px symbol-circle me-5">
                            <img src="' . $imagePath . '" alt="image" class="symbol-label">
                        </div>';
                }
                return '-';
            })
            ->addColumn('title', fn ($row) => $row->title_ar)
            ->addColumn('published_date', fn ($row) => $row->published_date)
            ->addColumn('status', fn ($row) => view('admin.paper_editions.parts.status', ['edition' => $row])->render())
            ->addColumn('actions', fn ($row) => view('admin.paper_editions.parts.actions', ['edition' => $row])->render())
            ->rawColumns(['cover_image', 'title', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.paper_editions.add', self::$data + ['info' => null]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'cover_image' => 'nullable|string|max:255',
            'pdf_file' => 'required|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'published_date', 'cover_image', 'pdf_file']);
        $data['status'] = $request->boolean('status', true);

        PaperEdition::create($data);

        return redirect()->route('paper_editions.view')->with('success', __('app.insert_success') ?? 'Added successfully');
    }

    public function getEdit($id)
    {
        try {
            $edition = PaperEdition::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('paper_editions.view')->with('danger', __('app.not_found'));
        }

        return view('admin.paper_editions.add', self::$data + ['info' => $edition]);
    }

    public function postEdit(Request $request, $id)
    {
        try {
            $edition = PaperEdition::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('paper_editions.view')->with('danger', __('app.not_found'));
        }

        $request->validate([
            'title_ar' => 'required|string|max:191',
            'title_en' => 'nullable|string|max:191',
            'cover_image' => 'nullable|string|max:255',
            'pdf_file' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        $data = $request->only(['title_ar', 'title_en', 'published_date', 'cover_image', 'pdf_file']);
        $data['status'] = $request->boolean('status', true);

        $edition->update($data);

        return redirect()->route('paper_editions.view')->with('success', __('app.update_success') ?? 'Updated successfully');
    }

    public function postStatus(Request $request)
    {
        try {
            $edition = PaperEdition::findOrFail(Crypt::decrypt($request->id));
            $edition->update(['status' => !$edition->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $edition = PaperEdition::findOrFail(Crypt::decrypt($request->id));
            $edition->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
