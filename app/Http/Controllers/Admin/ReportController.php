<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request)
    {
        $studentIds = $this->filteredStudentIds($request);
        $totalStudents = $studentIds->count();

        $baseRecords = $this->recordsQueryFor($studentIds, $request);

        $present = (clone $baseRecords)->where('type', 'check_in')->pluck('student_id')->unique()->count();
        $checkIns = (clone $baseRecords)->where('type', 'check_in')->count();
        $checkOuts = (clone $baseRecords)->where('type', 'check_out')->count();

        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $didNotCheckIn = ($dateFrom && $dateFrom === $dateTo) ? ($totalStudents - $present) : null;

        $records = (clone $baseRecords)
            ->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->paginate(25)
            ->withQueryString();

        $sections = Section::orderBy('name')->get();
        $gradeLevels = Section::select('grade_level')->distinct()->pluck('grade_level');
        $schoolYears = Section::select('school_year')->distinct()->orderByDesc('school_year')->pluck('school_year');

        return view('admin.reports.index', compact(
            'totalStudents', 'present', 'checkIns', 'checkOuts', 'didNotCheckIn',
            'records', 'sections', 'gradeLevels', 'schoolYears'
        ))->with($this->notificationBellData());
    }

    public function exportPdf(Request $request)
    {
        $records = $this->exportableRecords($request);

        $pdf = Pdf::loadView('admin.reports.pdf', compact('records'));

        return $pdf->download('attendance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $records = $this->exportableRecords($request);

        return Excel::download(
            new AttendanceExport($records),
            'attendance-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $records = $this->exportableRecords($request);

        return Excel::download(
            new AttendanceExport($records),
            'attendance-report-' . now()->format('Y-m-d') . '.csv'
        );
    }

    private function exportableRecords(Request $request)
    {
        $studentIds = $this->filteredStudentIds($request);

        return $this->recordsQueryFor($studentIds, $request)
            ->orderBy('attendance_date')
            ->orderBy('scanned_at')
            ->get();
    }

    private function recordsQueryFor(Collection $studentIds, Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $query = AttendanceRecord::whereIn('student_id', $studentIds)->with('student.section');

        if ($dateFrom && $dateTo) {
            $query->whereBetween('attendance_date', [$dateFrom, $dateTo]);
        }

        return $query;
    }

    private function filteredStudentIds(Request $request)
    {
        $studentsQuery = Student::query();

        if ($request->filled('school_year')) {
            $studentsQuery->whereHas('section', fn ($q) => $q->where('school_year', $request->school_year));
        }
        if ($request->filled('grade_level')) {
            $studentsQuery->whereHas('section', fn ($q) => $q->where('grade_level', $request->grade_level));
        }
        if ($request->filled('section_id')) {
            $studentsQuery->where('section_id', $request->section_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('lrn', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return $studentsQuery->pluck('id');
    }

    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('date')) {
            return [$request->date, $request->date];
        }

        if ($request->filled('week') && str_contains($request->week, '-W')) {
            [$year, $week] = explode('-W', $request->week);
            $start = Carbon::now()->setISODate((int) $year, (int) $week, 1);
            $end = (clone $start)->addDays(6);
            return [$start->toDateString(), $end->toDateString()];
        }

        if ($request->filled('month')) {
            $start = Carbon::parse($request->month . '-01')->startOfMonth();
            $end = (clone $start)->endOfMonth();
            return [$start->toDateString(), $end->toDateString()];
        }

        return [null, null];
    }
}