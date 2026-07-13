<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StudentRequest;
use App\Models\Student;
use App\Models\Branch;
use App\Models\Region;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class StudentsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'students';
    }

    public function getIndex()
    {
        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();

        return view('admin.students.view', self::$data + compact('branches', 'regions'));
    }

    public function getList(Request $request)
    {
        $query = Student::with('branch')->withCount('registrations');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('is_child')) {
            $query->where('is_child', $request->is_child);
        }
        if ($request->filled('name')) {
            $search = $request->name;
            $query->where(function($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                  ->orWhere('full_name_en', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        $datatable = DataTables::of($query);

        $datatable->addColumn('name', function ($row) {
            return app()->getLocale() == 'ar' ? $row->full_name_ar : $row->full_name_en;
        });

        $datatable->addColumn('branch', function ($row) {
            return $row->branch ? (app()->getLocale() == 'ar' ? $row->branch->name_ar : $row->branch->name_en) : '-';
        });

        $datatable->editColumn('status', function ($row) {
            return view('admin.students.parts.status', ['student' => $row])->render();
        });

        $datatable->addColumn('actions', function ($row) {
            return view('admin.students.parts.actions', ['student' => $row])->render();
        });

        $datatable->rawColumns(['status', 'actions']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function getAdd()
    {
        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();
        $guardians = Guardian::where('is_active', 1)->get();

        return view('admin.students.add', self::$data + [
            'info' => null,
            'branches' => $branches,
            'regions' => $regions,
            'guardians' => $guardians,
        ]);
    }

    public function postAdd(StudentRequest $request)
    {
        $data = $request->safe()->except(['image', 'password']) + [
            'password' => Hash::make($request->password),
            'status' => $request->boolean('status', true)
        ];

        $student = Student::create($data);

        if ($request->hasFile('image')) {
            $student->update(['image' => $request->file('image')->store('students', 'public')]);
        }

        return redirect()->route('students.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        $branches = Branch::where('status', 1)->get();
        $regions = Region::where('status', 1)->get();
        $guardians = Guardian::where('is_active', 1)->get();

        return view('admin.students.add', self::$data + [
            'info' => $student,
            'branches' => $branches,
            'regions' => $regions,
            'guardians' => $guardians,
        ]);
    }

    public function postEdit(StudentRequest $request, $id)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        $data = $request->safe()->except(['image', 'password']) + [
            'status' => $request->boolean('status', true)
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('students', 'public');
        }

        $student->update($data);

        return redirect()->route('students.view')->with('success', __('app.update_success'));
    }

    public function getView(Request $request, $id)
    {
        try {
            $student = Student::with(['branch', 'guardian', 'registrations.subject', 'payments'])
                ->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('students.view')->with('danger', __('app.not_found'));
        }

        return view('admin.students.show', self::$data + ['student' => $student]);
    }

    public function postStatus(Request $request)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($request->id));
            $student->update(['status' => ! $student->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $student = Student::findOrFail(Crypt::decrypt($request->id));

            if ($student->registrations()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $student->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
