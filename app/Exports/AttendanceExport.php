<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected Collection $records)
    {
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return ['Date', 'LRN', 'Student Name', 'Section', 'Type', 'Time'];
    }

    public function map($record): array
    {
        return [
            $record->attendance_date->format('Y-m-d'),
            $record->student->lrn,
            $record->student->full_name,
            $record->student->section->name ?? '',
            $record->type === 'check_in' ? 'Check-In' : 'Check-Out',
            $record->scanned_at->format('h:i A'),
        ];
    }
}