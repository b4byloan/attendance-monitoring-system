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
    <h1>Student List</h1>
    <p>Generated {{ now()->format('F j, Y h:i A') }}</p>
    <p>
        Search: {{ $filters['search'] !== '' ? $filters['search'] : 'All students' }}
        &middot; Subject: {{ $filters['subject'] }}
        &middot; Timestamp: {{ $filters['date'] }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>LRN</th>
                <th>Student Name</th>
                <th>Section</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    <td>{{ $record->scanned_at->format('Y-m-d h:i A') }}</td>
                    <td>{{ $record->student->lrn }}</td>
                    <td>{{ $record->student->full_name }}</td>
                    <td><x-ui.optional-value :value="$record->student->section->name ?? null" /></td>
                    <td><x-ui.optional-value :value="$record->subject->name ?? null" /></td>
                    <td>{{ ucfirst($record->status) }}</td>
                    <td><x-ui.optional-value :value="$record->recordedBy->name ?? null" /></td>
                </tr>
            @empty
                <tr><td colspan="7">No scanned students match these filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
