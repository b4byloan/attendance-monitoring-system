<?php

namespace App\Http\Controllers\Concerns;

use App\Models\AttendanceRecord;
use App\Models\Section;
use App\Models\SubjectAttendanceRecord;
use App\Models\User;
use Illuminate\Support\Collection;

trait LoadsSectionOverview
{
    /**
     * Sections handled by the user with today's student, gate check-in and
     * class-scan totals attached.
     *
     * @return Collection<int, Section>
     */
    protected function sectionsWithTodayCounts(User $user): Collection
    {
        $sections = $user->sections()
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $sectionIds = $sections->pluck('id');
        $today = now()->toDateString();

        $presentBySection = AttendanceRecord::query()
            ->join('students', 'students.id', '=', 'attendance_records.student_id')
            ->whereIn('students.section_id', $sectionIds)
            ->where('attendance_records.type', 'check_in')
            ->whereDate('attendance_records.attendance_date', $today)
            ->selectRaw('students.section_id, COUNT(DISTINCT attendance_records.student_id) as present_count')
            ->groupBy('students.section_id')
            ->pluck('present_count', 'section_id');

        $scannedBySection = SubjectAttendanceRecord::query()
            ->join('students', 'students.id', '=', 'subject_attendance_records.student_id')
            ->whereIn('students.section_id', $sectionIds)
            ->whereDate('subject_attendance_records.attendance_date', $today)
            ->selectRaw('students.section_id, COUNT(*) as scan_count')
            ->groupBy('students.section_id')
            ->pluck('scan_count', 'section_id');

        return $sections->each(function (Section $section) use ($presentBySection, $scannedBySection): void {
            $section->present_today = (int) $presentBySection->get($section->id, 0);
            $section->scanned_today = (int) $scannedBySection->get($section->id, 0);
        });
    }
}
