<?php

namespace App\Http\Controllers;

use App\Exports\StudentScanExport;
use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Http\Controllers\Concerns\LoadsSectionOverview;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SubAdminStudentController extends Controller
{
    use LoadsNotificationBell;
    use LoadsSectionOverview;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $sectionId = $request->integer('section');

        $students = $this->assignedStudentsQuery($request)
            ->when($sectionId, fn (Builder $query) => $query->where('section_id', $sectionId))
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return view('sub-admin.students.index', [
            'students' => $students,
            'todayStatuses' => $this->todayStatuses($students->getCollection()->pluck('id')),
            'search' => $search,
            'sections' => $this->assignedSections($request->user()),
            'activeSection' => null,
            'selectedSectionId' => $sectionId,
        ])->with($this->notificationBellData());
    }

    public function sections(Request $request): View
    {
        return view('sub-admin.sections.index', [
            'sections' => $this->sectionsWithTodayCounts($request->user()),
        ])->with($this->notificationBellData());
    }

    public function section(Request $request, Section $section): View
    {
        $this->ensureAssignedTo($request, $section);

        $search = trim((string) $request->input('search'));

        $students = $section->students()
            ->with('section')
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        return view('sub-admin.students.index', [
            'students' => $students,
            'todayStatuses' => $this->todayStatuses($students->getCollection()->pluck('id')),
            'search' => $search,
            'sections' => $this->assignedSections($request->user()),
            'activeSection' => $section,
            'selectedSectionId' => $section->id,
        ])->with($this->notificationBellData());
    }

    public function show(Request $request, Student $student): View
    {
        $student->load('section.adviser', 'guardians');

        $this->ensureAssignedTo($request, $student->section);

        $subjects = $this->availableSubjects($request->user());
        $selectedSubjectId = $request->integer('subject') ?: null;

        if ($selectedSubjectId && ! $subjects->contains('id', $selectedSubjectId)) {
            $selectedSubjectId = null;
        }

        $subjectQuery = $student->subjectAttendanceRecords()
            ->with('subject', 'recordedBy')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->when($selectedSubjectId, fn (Builder $query) => $query->where('subject_id', $selectedSubjectId));

        $todayRecords = $student->attendanceRecords()
            ->whereDate('attendance_date', now()->toDateString())
            ->orderBy('scanned_at')
            ->get();

        $recentRecords = $student->attendanceRecords()
            ->orderByDesc('attendance_date')
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        return view('sub-admin.students.show', [
            'student' => $student,
            'todayStatus' => $this->todayStatuses(collect([$student->id]))->get($student->id, 'not_yet'),
            'todayCheckIn' => $todayRecords->firstWhere('type', 'check_in'),
            'todayCheckOut' => $todayRecords->firstWhere('type', 'check_out'),
            'recentRecords' => $recentRecords,
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'subjectRecords' => $subjectQuery->orderByDesc('attendance_date')
                ->orderByDesc('scanned_at')
                ->paginate(10)
                ->withQueryString(),
            'activeTab' => $request->input('tab', 'information'),
        ])->with($this->notificationBellData());
    }

    public function roster(Request $request): View
    {
        $tab = $request->input('tab') === 'sections' ? 'sections' : 'attendance';

        if ($tab === 'sections') {
            $sections = $this->sectionsWithTodayCounts($request->user());

            return view('sub-admin.roster', [
                'tab' => $tab,
                'sections' => $sections,
                'sectionTabCount' => $sections->count(),
            ])->with($this->notificationBellData());
        }

        $records = $this->scanRecordsQuery($request)
            ->orderByDesc('scanned_at')
            ->paginate(15)
            ->withQueryString();

        return view('sub-admin.roster', [
            'tab' => $tab,
            'records' => $records,
            'subjects' => $this->scanSubjects($request),
            'sectionTabCount' => $this->assignedSections($request->user())->count(),
            'search' => trim((string) $request->input('search')),
            'selectedSubjectId' => $request->integer('subject') ?: null,
            'selectedDate' => (string) $request->input('date'),
        ])->with($this->notificationBellData());
    }

    public function exportPdf(Request $request)
    {
        $pdf = Pdf::loadView('sub-admin.roster-pdf', [
            'records' => $this->exportableScanRecords($request),
            'filters' => $this->scanFilterSummary($request),
        ]);

        return $pdf->download('student-list-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new StudentScanExport($this->exportableScanRecords($request)),
            'student-list-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new StudentScanExport($this->exportableScanRecords($request)),
            'student-list-'.now()->format('Y-m-d').'.csv'
        );
    }

    private function assignedStudentsQuery(Request $request): Builder
    {
        return Student::query()
            ->with('section')
            ->whereIn('section_id', $this->assignedSections($request->user())->pluck('id'));
    }

    /** @return Collection<int, Section> */
    private function assignedSections(User $user): Collection
    {
        return $user->sections()->orderBy('name')->get();
    }

    /** @return Collection<int, Subject> */
    private function availableSubjects(User $user): Collection
    {
        return $user->subjects()
            ->where('subjects.status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Class attendance scans recorded for students in the user's sections,
     * across every subject and section they handle.
     */
    private function scanRecordsQuery(Request $request): Builder
    {
        $search = trim((string) $request->input('search'));

        return $this->scanScopeQuery($request)
            ->with(['student.section', 'subject', 'recordedBy'])
            ->when($request->integer('subject'), fn (Builder $query, int $subjectId) => $query->where('subject_id', $subjectId))
            ->when($request->filled('date'), fn (Builder $query) => $query->whereDate('attendance_date', $request->input('date')))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->whereHas('student', function (Builder $student) use ($search): void {
                    $student->where(function (Builder $student) use ($search): void {
                        $student->where('lrn', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            });
    }

    private function scanScopeQuery(Request $request): Builder
    {
        return SubjectAttendanceRecord::query()
            ->whereIn('student_id', Student::query()
                ->select('id')
                ->whereIn('section_id', $this->assignedSections($request->user())->pluck('id')));
    }

    /** @return Collection<int, Subject> */
    private function scanSubjects(Request $request): Collection
    {
        $subjectIds = $this->scanScopeQuery($request)
            ->select('subject_id')
            ->distinct()
            ->pluck('subject_id');

        return Subject::query()
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return Collection<int, SubjectAttendanceRecord> */
    private function exportableScanRecords(Request $request): Collection
    {
        return $this->scanRecordsQuery($request)
            ->orderBy('scanned_at')
            ->get();
    }

    /** @return array{search: string, subject: string, date: string} */
    private function scanFilterSummary(Request $request): array
    {
        $subjectName = $request->integer('subject')
            ? Subject::find($request->integer('subject'))?->name
            : null;

        return [
            'search' => trim((string) $request->input('search')),
            'subject' => $subjectName ?? 'All subjects',
            'date' => (string) ($request->input('date') ?: 'All dates'),
        ];
    }

    private function applySearch(Builder $query, string $search): void
    {
        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $query->where(function (Builder $query) use ($terms): void {
            foreach ($terms as $term) {
                $query->where(function (Builder $query) use ($term): void {
                    if (ctype_digit($term)) {
                        $query->orWhere('id', $term);
                    }

                    $query->orWhere('lrn', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('middle_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
            }
        });
    }

    /** @return Collection<int, string> */
    private function todayStatuses(Collection $studentIds): Collection
    {
        return AttendanceRecord::query()
            ->whereIn('student_id', $studentIds)
            ->whereDate('attendance_date', now()->toDateString())
            ->get()
            ->groupBy('student_id')
            ->map(fn (Collection $records): string => match (true) {
                $records->contains('type', 'check_in') && $records->contains('type', 'check_out') => 'checked_out',
                $records->contains('type', 'check_in') => 'checked_in',
                default => 'not_yet',
            });
    }

    private function ensureAssignedTo(Request $request, ?Section $section): void
    {
        $assigned = $section !== null
            && $request->user()->sections()->whereKey($section->id)->exists();

        abort_unless($assigned, 403, 'You can only view students in your assigned sections.');
    }
}
