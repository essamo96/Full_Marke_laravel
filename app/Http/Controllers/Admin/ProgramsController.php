<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
        $programs = Program::withCount('subjects')->orderBy('order')->paginate(15);

        return view('admin.programs.view', self::$data + ['programs' => $programs]);
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
