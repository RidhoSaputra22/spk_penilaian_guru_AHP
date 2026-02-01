<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = ActivityLog::with('user')
            ->whereHas('user', function($q) use ($institution) {
                $q->where('institution_id', $institution?->id);
            });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // User filter
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        // Get distinct actions for filter
        $actions = ActivityLog::whereHas('user', function($q) use ($institution) {
            $q->where('institution_id', $institution?->id);
        })->distinct()->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'actions'));
    }
}
