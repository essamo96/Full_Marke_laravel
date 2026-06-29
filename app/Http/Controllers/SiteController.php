<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function programDetails(Program $program): View
    {
        $program->load('subjects.groups');

        return view('site.program-details', compact('program'));
    }

    public function applyNow(Request $request): View
    {
        $programs = Program::where('is_active', true)->orderBy('order')->with('subjects')->get();
        $branches = Branch::active()->orderBy('name')->get();
        $selectedProgram = $request->query('program');

        return view('site.apply-now', compact('programs', 'branches', 'selectedProgram'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_ar' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'image' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'major_profession' => 'nullable|string|max:255',
            'health_information' => 'nullable|string',
            'program_id' => 'nullable|exists:programs,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'message' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('applications', 'public');
        }

        Application::create($data);

        return redirect()
            ->route('apply.create')
            ->with('applied', true);
    }
}
