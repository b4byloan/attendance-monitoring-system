<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsNotificationBell;
use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SubAdminStudentController extends Controller
{
    use LoadsNotificationBell;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $students = $this->assignedStudentsQuery($request)
            ->when($search !== '', fn (Builder $query) => $this->applySearch($query, $search))
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        return view('sub-admin.students.index', [
            'students' => $students,
            'todayStatuses' => $this->todayStatuses($students->getCollection()->pluck('id')),
            'search' => $search,
            'activeSection' => null,
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
            ->paginate(20)
            ->withQueryString();

        return view('sub-admin.students.index', [
            'students' => $students,
            'todayStatuses' => $this->todayStatuses($students->getCollection()->pluck('id')),
            'search' => $search,
            'activeSection' => $section,
        ])->with($this->notificationBellData());
    }

    public function show(Request $request, Student $student): View
    {
        $student->load('section.adviser', 'guardians');

        $this->ensureAssignedTo($request, $student->section);

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
        ])->with($this->notificationBellData());
    }

    public function roster(Request $request): View
    {
        $sections = Section::query()
            ->where('adviser_id', $request->user()->id)
            ->with(['students' => fn ($query) => $query->orderBy('last_name')])
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $studentIds = $sections->flatMap(fn (Section $section) => $section->students)->pluck('id');

        $presentStudentIds = AttendanceRecord::query()
            ->whereIn('student_id', $studentIds)
            ->where('type', 'check_in')
            ->whereDate('attendance_date', now()->toDateString())
            ->distinct()
            ->pluck('student_id');

        $sections->each(function (Section $section) use ($presentStudentIds): void {
            $section->present_today = $section->students->whereIn('id', $presentStudentIds)->count();
        });

        return view('sub-admin.roster', [
            'sections' => $sections,
            'todayStatuses' => $this->todayStatuses($studentIds),
        ])->with($this->notificationBellData());
    }

    private function assignedStudentsQuery(Request $request): Builder
    {
        return Student::query()
            ->with('section')
            ->whereHas('section', fn (Builder $query) => $query->where('adviser_id', $request->user()->id));
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query->where('id', $search)
                ->orWhere('lrn', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
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
        abort_unless(
            $section !== null && $section->adviser_id === $request->user()->id,
            403,
            'You can only view students in your assigned sections.'
        );
    }
}
