<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Attendance History</h1>
            <x-ui.back-button :href="route('parent.students.dashboard', $student)" label="Back to dashboard" />
        </div>

        @if ($students->count() > 1)
            <div class="flex gap-2 mb-6">
                @foreach ($students as $s)
                    <a href="{{ route('parent.students.attendance', $s) }}"
                       class="px-4 py-2 rounded {{ $s->id === $student->id ? 'bg-blue-600 text-white' : 'bg-white border text-gray-700' }}">
                        {{ $s->full_name }}
                    </a>
                @endforeach
            </div>
        @endif

        <h2 class="text-lg font-semibold mb-2">{{ $student->full_name }}</h2>

        <form method="GET" class="bg-white p-4 rounded-lg shadow mb-4 flex gap-3 items-end">
            <div>
                <label class="block text-sm text-gray-500 mb-1">Specific date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-500 mb-1">Or whole month</label>
                <input type="month" name="month" value="{{ request('month') }}" class="border rounded px-3 py-2">
            </div>
            <button class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Filter</button>
            @if (request('date') || request('month'))
                <a href="{{ route('parent.students.attendance', $student) }}" class="text-sm text-gray-500">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($records as $record)
                        <tr>
                            <td class="px-4 py-3">{{ $record->attendance_date->format('M j, Y') }}</td>
                            <td class="px-4 py-3">{{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</td>
                            <td class="px-4 py-3">{{ $record->scanned_at->format('h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">No records for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $records->links() }}</div>
    </div>
</body>
</html>