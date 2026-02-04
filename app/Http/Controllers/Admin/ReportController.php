<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $periods = AssessmentPeriod::where('institution_id', auth()->user()->institution_id)
            ->orderByDesc('scoring_open_at')
            ->get();

        return view('admin.reports.index', compact('periods'));
    }

    public function generate()
    {
        $periods = AssessmentPeriod::where('institution_id', auth()->user()->institution_id)
            ->orderByDesc('scoring_open_at')
            ->get();

        return view('admin.reports.generate', compact('periods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:results,progress,ahp',
            'period_id' => 'required|exists:assessment_periods,id',
            'format' => 'required|in:pdf,excel',
        ]);

        // Redirect based on report type and format
        switch ($validated['report_type']) {
            case 'results':
                return redirect()->route('admin.results.export', [
                    'period_id' => $validated['period_id'],
                    'format' => $validated['format']
                ]);

            case 'progress':
                return redirect()->route('admin.reports.export-progress', [
                    'period_id' => $validated['period_id'],
                    'format' => $validated['format']
                ]);

            case 'ahp':
                return redirect()->route('admin.reports.export-ahp', [
                    'period_id' => $validated['period_id'],
                    'format' => $validated['format']
                ]);

            default:
                return back()->with('error', 'Jenis laporan tidak valid');
        }
    }

    public function exportProgress(Request $request)
    {
        $periodId = $request->query('period_id');
        $format = $request->query('format', 'pdf');

        $period = AssessmentPeriod::findOrFail($periodId);

        $assessments = \App\Models\Assessment::with(['teacher.user', 'assessor.user'])
            ->where('assessment_period_id', $periodId)
            ->orderBy('status')
            ->get();

        $stats = [
            'total' => $assessments->count(),
            'pending' => $assessments->where('status', 'pending')->count(),
            'in_progress' => $assessments->where('status', 'in_progress')->count(),
            'completed' => $assessments->whereIn('status', ['submitted', 'finalized'])->count(),
        ];

        if ($format === 'excel') {
            return $this->exportProgressExcel($period, $assessments, $stats);
        }

        return view('admin.reports.export-progress-pdf', compact('period', 'assessments', 'stats'));
    }

    private function exportProgressExcel($period, $assessments, $stats)
    {
        $filename = 'laporan-progress-' . \Str::slug($period->name) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($assessments, $stats, $period) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header info
            fputcsv($file, ['LAPORAN PROGRESS PENILAIAN GURU']);
            fputcsv($file, ['Periode', $period->name . ' - ' . $period->academic_year]);
            fputcsv($file, ['Tanggal Export', now()->format('d M Y H:i')]);
            fputcsv($file, []);

            // Stats
            fputcsv($file, ['STATISTIK']);
            fputcsv($file, ['Total Penilaian', $stats['total']]);
            fputcsv($file, ['Pending', $stats['pending']]);
            fputcsv($file, ['In Progress', $stats['in_progress']]);
            fputcsv($file, ['Completed', $stats['completed']]);
            fputcsv($file, []);

            // Table header
            fputcsv($file, ['No', 'Nama Guru', 'Penilai', 'Status', 'Tanggal Mulai', 'Tanggal Selesai']);

            // Table data
            foreach ($assessments as $index => $assessment) {
                fputcsv($file, [
                    $index + 1,
                    $assessment->teacher->user->name ?? '-',
                    $assessment->assessor->user->name ?? '-',
                    ucfirst($assessment->status),
                    $assessment->started_at?->format('d M Y H:i') ?? '-',
                    $assessment->submitted_at?->format('d M Y H:i') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAhp(Request $request)
    {
        $periodId = $request->query('period_id');
        $format = $request->query('format', 'pdf');

        $period = AssessmentPeriod::with('ahpModel')->findOrFail($periodId);
        $ahpModel = $period->ahpModel;

        if (!$ahpModel) {
            return back()->with('error', 'Belum ada model AHP untuk periode ini');
        }

        // Get goal node first, then get its children (criteria)
        $goal = \App\Models\CriteriaNode::where('criteria_set_id', $ahpModel->criteria_set_id)
            ->where('node_type', 'goal')
            ->first();

        $criteria = collect();
        if ($goal) {
            $criteria = \App\Models\CriteriaNode::where('parent_id', $goal->id)
                ->orderBy('sort_order')
                ->get();
        }

        $weights = \App\Models\AhpWeight::where('ahp_model_id', $ahpModel->id)
            ->whereIn('criteria_node_id', $criteria->pluck('id'))
            ->get()
            ->keyBy('criteria_node_id');

        $comparisons = \App\Models\AhpComparison::where('ahp_model_id', $ahpModel->id)
            ->get();

        if ($format === 'excel') {
            return $this->exportAhpExcel($period, $ahpModel, $criteria, $weights, $comparisons);
        }

        return view('admin.reports.export-ahp-pdf', compact('period', 'ahpModel', 'criteria', 'weights', 'comparisons'));
    }

    private function exportAhpExcel($period, $ahpModel, $criteria, $weights, $comparisons)
    {
        $filename = 'laporan-bobot-ahp-' . \Str::slug($period->name) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($criteria, $weights, $period, $ahpModel) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header info
            fputcsv($file, ['LAPORAN BOBOT AHP']);
            fputcsv($file, ['Periode', $period->name . ' - ' . $period->academic_year]);
            fputcsv($file, ['Consistency Ratio', number_format($ahpModel->consistency_ratio ?? 0, 4)]);
            fputcsv($file, ['Status', $ahpModel->status === 'finalized' ? 'Finalized' : 'Draft']);
            fputcsv($file, ['Tanggal Export', now()->format('d M Y H:i')]);
            fputcsv($file, []);

            // Weights table
            fputcsv($file, ['BOBOT KRITERIA']);
            fputcsv($file, ['No', 'Kriteria', 'Bobot', 'Persentase']);

            foreach ($criteria as $index => $criterion) {
                $weight = $weights->get($criterion->id);
                $weightValue = $weight ? $weight->weight : 0;

                fputcsv($file, [
                    $index + 1,
                    $criterion->name,
                    number_format($weightValue, 4),
                    number_format($weightValue * 100, 2) . '%',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
