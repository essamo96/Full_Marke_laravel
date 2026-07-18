<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subject;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\EducationalLesson;
use App\Models\SubjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SubjectContentController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'subject_content';
    }

    public function index()
    {
        $subjects = Subject::with('program')->get();
        return view('admin.subject_content.index', self::$data + compact('subjects'));
    }

    public function manage($id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subject_content.view')->with('danger', __('app.not_found'));
        }

        // Educational Stages (to link units to)
        $stages = EducationalStage::where('is_active', true)->get();

        // Get units (we get units that have resources for this subject, or we can just fetch all units, but usually a subject is tied to a specific program/stage).
        // For flexibility, let's load units that are active.
        $units = EducationalUnit::with(['lessons' => function($q) use ($subject) {
            $q->whereHas('resources', function($q2) use ($subject) {
                $q2->where('subject_id', $subject->id);
            })->orWhereDoesntHave('resources');
        }, 'lessons.resources' => function($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        }])->where('is_active', true)->get();

        return view('admin.subject_content.manage', self::$data + compact('subject', 'stages', 'units'));
    }
}
