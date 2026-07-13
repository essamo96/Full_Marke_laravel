<?php

namespace App\Http\Controllers\Admin;

use App\Models\Program;
use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'registrations';
        $this->path = 'registrations';
    }

    public function getIndex(Request $request)
    {
        $query = Registration::with(['student', 'subject.program', 'group']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_id')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('full_name_ar', 'like', "%{$search}%")
                            ->orWhere('full_name_en', 'like', "%{$search}%");
                    });
            });
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();

        return view('admin.registrations.view', self::$data + [
            'registrations' => $registrations,
            'programs' => Program::orderBy('sort_order')->get()]);
    }
}
