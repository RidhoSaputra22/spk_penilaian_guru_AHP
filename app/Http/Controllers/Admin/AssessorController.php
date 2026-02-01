<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessorProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\KpiFormAssignment;
use Illuminate\Http\Request;

class AssessorController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = AssessorProfile::with(['user'])
            ->whereHas('user', function($q) use ($institution) {
                $q->where('institution_id', $institution?->id);
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('assessor_type', $request->type);
        }

        $assessors = $query->latest()->paginate(10)->withQueryString();

        // Get active period
        $activePeriod = AssessmentPeriod::where('institution_id', $institution?->id)
            ->where('status', 'open')
            ->first();

        // Add assessment stats
        if ($activePeriod) {
            $assessors->getCollection()->transform(function($assessor) use ($activePeriod) {
                $assignments = KpiFormAssignment::where('assessor_id', $assessor->id)
                    ->where('period_id', $activePeriod->id)
                    ->get();

                $assessor->total_assignments = $assignments->count();
                $assessor->completed_assignments = $assignments->where('status', 'completed')->count();

                return $assessor;
            });
        }

        return view('admin.assessors.index', compact('assessors', 'activePeriod'));
    }
}
