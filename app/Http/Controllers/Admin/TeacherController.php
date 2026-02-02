<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TeacherGroup;
use App\Models\User;
use App\Models\Role;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = TeacherProfile::with(['user', 'teacherGroup'])
            ->whereHas('user', function($q) use ($institution) {
                $q->where('institution_id', $institution?->id);
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('nuptk', 'like', "%{$search}%");
            });
        }

        // Group filter
        if ($request->filled('group')) {
            $query->where('teacher_group_id', $request->group);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->whereHas('user', function($q) use ($request) {
                if ($request->status === 'active') {
                    $q->whereNull('deactivated_at');
                } else {
                    $q->whereNotNull('deactivated_at');
                }
            });
        }

        $teachers = $query->latest()->paginate(10)->withQueryString();
        $groups = TeacherGroup::where('institution_id', $institution?->id)->get();

        // Get active period for assessment stats
        $activePeriod = AssessmentPeriod::where('institution_id', $institution?->id)
            ->where('status', 'open')
            ->first();

        // Add assessment stats to each teacher
        if ($activePeriod) {
            $teachers->getCollection()->transform(function($teacher) use ($activePeriod) {
                $assessment = Assessment::where('teacher_profile_id', $teacher->id)
                    ->where('period_id', $activePeriod->id)
                    ->first();

                $teacher->current_assessment_status = $assessment?->status ?? 'not_assigned';
                return $teacher;
            });
        }

        return view('admin.teachers.index', compact('teachers', 'groups', 'activePeriod'));
    }
}
