<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Group;
use Illuminate\Http\Request;

class ResourceLibraryController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'resource-library';
    }
    /**
     * Display the Resource Library grouping resources by Subject -> Group -> Curriculum.
     */
    public function index(Request $request)
    {
        // Load all active subjects with their programs
        $subjects = Subject::with('program')->orderBy('name_ar')->get();
        
        $selectedSubjectId = $request->query('subject_id') ? (int) $request->query('subject_id') : null;
        if ($selectedSubjectId && !$subjects->contains('id', $selectedSubjectId)) {
            $selectedSubjectId = null;
        }

        $selectedSubject = null;
        $groups = collect();
        $units = collect();
        
        if ($selectedSubjectId) {
            $selectedSubject = Subject::findOrFail($selectedSubjectId);
            
            // Load groups for this subject
            $groups = Group::where('subject_id', $selectedSubjectId)->orderBy('name')->get();
            
            // To show the curriculum, we load units and lessons for the selected subject.
            // When looping over these in the view for a specific group, we will filter the resources
            // by calling `->filter(fn($r) => in_array($group->id, $r->group_ids ?? []) || empty($r->group_ids))`
            $units = \App\Models\EducationalUnit::whereHas('stage', function ($q) use ($selectedSubjectId) {
                    $q->where('subject_id', $selectedSubjectId);
                })
                ->where('is_active', true)
                ->with(['lessons' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }, 'lessons.resources' => function ($q) {
                    $q->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
        }

        return view('admin.resource_library.index', self::$data + compact('subjects', 'selectedSubject', 'selectedSubjectId', 'groups', 'units'));
    }
}
