<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Announcements</h1>
            <x-ui.back-button :href="route('parent.dashboard')" label="Back to dashboard" />
        </div>

        <div class="space-y-3">
            @forelse ($announcements as $a)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-2">
                        @if ($a->is_pinned)<span class="text-yellow-600"><x-ui.icon name="pin" class="h-3.5 w-3.5" /></span>@endif
                        <h2 class="font-bold">{{ $a->title }}</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $a->isSchoolWide() ? 'School-wide' : ($a->section->name ?? 'Section') }} · {{ $a->publish_date->format('M j, Y') }}
                    </p>
                    <p class="text-sm text-gray-700 mt-2">{{ $a->description }}</p>
                </div>
            @empty
                <p class="text-gray-400 text-center py-8">No announcements right now.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $announcements->links() }}</div>
    </div>
</body>
</html>