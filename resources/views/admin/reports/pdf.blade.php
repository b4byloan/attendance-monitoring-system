<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 0; }
        p { color: #666; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <p>Generated {{ now()->format('F j, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>LRN</th>
                <th>Student Name</th>
                <th>Section</th>
                <th>Type</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    <td>{{ $record->attendance_date->format('Y-m-d') }}</td>
                    <td>{{ $record->student->lrn }}</td>
                    <td>{{ $record->student->full_name }}</td>
                    <td>{{ $record->student->section->name ?? '' }}</td>
                    <td>{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</td>
                    <td>{{ $record->scanned_at->format('h:i A') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No records match these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>