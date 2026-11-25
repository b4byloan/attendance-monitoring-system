<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentScanExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $records) {}

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return ['Timestamp', 'LRN', 'Student Name', 'Section', 'Subject', 'Status', 'Recorded By'];
    }

    public function map($record): array
    {
        return [
            $record->scanned_at->format('Y-m-d h:i A'),
            $record->student->lrn,
            $record->student->full_name,
            $record->student->section->name ?? '',
            $record->subject->name ?? '',
            ucfirst($record->status),
            $record->recordedBy->name ?? '',
        ];
    }
}
