<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class ProgramsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'programs';
        $this->path = 'programs';
    }

    public function getIndex()
    {
        return view('admin.programs.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('search_value');

        $programs = Program::withCount('subjects')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")))
            ->orderBy('order');

        return DataTables::of($programs)
            ->addColumn('image', fn ($program) => view('admin.programs.parts.image', ['program' => $program])->render())
            ->addColumn('name', fn ($program) => view('admin.programs.parts.name', ['program' => $program])->render())
            ->addColumn('type', fn ($program) => '<span class="badge badge-light-info">' . __('app.type_'.$program->type) . '</span>')
            ->addColumn('subjects_count', fn ($program) => $program->subjects_count)
            ->addColumn('status', fn ($program) => view('admin.programs.parts.status', ['program' => $program])->render())
            ->addColumn('actions', fn ($program) => view('admin.programs.parts.actions', ['program' => $program])->render())
            ->rawColumns(['image', 'name', 'type', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.programs.add', self::$data + ['info' => null]);
    }

    public function postAdd(ProgramRequest $request)
    {
        $program = Program::create($request->safe()->except('image') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->hasFile('image')) {
            $program->update(['image' => $request->file('image')->store('programs', 'public')]);
        }

        return redirect()->route('programs.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('programs.view')->with('danger', __('app.not_found'));
        }

        return view('admin.programs.add', self::$data + ['info' => $program]);
    }

    public function postEdit(ProgramRequest $request, $id)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('programs.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except('image') + [
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('programs', 'public');
        }

        $program->update($data);

        return redirect()->route('programs.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($request->id));
            $program->update(['is_active' => ! $program->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $program = Program::findOrFail(Crypt::decrypt($request->id));

            if ($program->subjects()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $program->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
