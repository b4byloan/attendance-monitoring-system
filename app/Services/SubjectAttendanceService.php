<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendanceRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SubjectAttendanceService
{
    /**
     * Class sessions (subject + date) held for the student's section, flagged
     * Present when the student was scanned and Away when they were not.
     *
     * @return array{sessions: LengthAwarePaginator, rows: Collection<int, array{subject: ?Subject, date: mixed, present: bool, time: mixed}>}
     */
    public function forStudent(Student $student, ?string $date = null, ?string $month = null, int $perPage = 10): array
    {
        $sessions = SubjectAttendanceRecord::query()
            ->where(function ($query) use ($student): void {
                $query->where('student_id', $student->id);

                if ($student->section_id) {
                    $query->orWhereHas('student', fn ($relation) => $relation->where('section_id', $student->section_id));
                }
            })
            ->when($date, fn ($query) => $query->whereDate('attendance_date', $date))
            ->when(! $date && $month, fn ($query) => $query->where('attendance_date', 'like', $month.'-%'))
            ->select('subject_id', 'attendance_date')
            ->distinct()
            ->orderByDesc('attendance_date')
            ->paginate($perPage, ['*'], 'subjects_page')
            ->withQueryString();

        if ($sessions->isEmpty()) {
            return ['sessions' => $sessions, 'rows' => collect()];
        }

        $dates = $sessions->pluck('attendance_date')->map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : (string) $value)->unique();

        $records = SubjectAttendanceRecord::query()
            ->where('student_id', $student->id)
            ->whereIn('subject_id', $sessions->pluck('subject_id')->unique())
            ->whereIn('attendance_date', $dates)
            ->get()
            ->keyBy(fn (SubjectAttendanceRecord $record) => $record->subject_id.'|'.$record->attendance_date->toDateString());

        $subjects = Subject::query()
            ->whereIn('id', $sessions->pluck('subject_id'))
            ->get()
            ->keyBy('id');

        $rows = $sessions->getCollection()->map(function (SubjectAttendanceRecord $session) use ($records, $subjects): array {
            $key = $session->subject_id.'|'.$session->attendance_date->toDateString();
            $record = $records->get($key);

            return [
                'subject' => $subjects->get($session->subject_id),
                'date' => $session->attendance_date,
                'present' => $record !== null,
                'time' => $record?->scanned_at,
            ];
        });

        return ['sessions' => $sessions, 'rows' => $rows];
    }
}
