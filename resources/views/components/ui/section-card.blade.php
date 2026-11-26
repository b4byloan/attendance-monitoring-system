@props(['section'])

<x-ui.card class="flex min-w-0 flex-col">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <h3 class="break-words text-lg font-bold text-gray-900">{{ $section->name }}</h3>
            <p class="break-words text-sm text-gray-500">{{ $section->grade_level }} · {{ $section->school_year }}</p>
        </div>
        <x-ui.badge color="{{ $section->status === 'active' ? 'green' : 'gray' }}">{{ ucfirst($section->status) }}</x-ui.badge>
    </div>

    <div class="mt-4 grid grid-cols-3 gap-2">
        <div class="rounded-lg bg-gray-50 p-3">
            <p class="text-[11px] leading-tight text-gray-500">Students</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $section->students_count }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-3">
            <p class="text-[11px] leading-tight text-gray-500">Present Today</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ $section->present_today }}</p>
        </div>
        <div class="rounded-lg bg-gray-50 p-3">
            <p class="text-[11px] leading-tight text-gray-500">Scanned Today</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $section->scanned_today }}</p>
        </div>
    </div>

    <x-ui.button href="{{ route('sub-admin.sections.students', $section) }}" class="mt-4 w-full">
        View Students <x-ui.icon name="arrow-right" class="h-4 w-4" />
    </x-ui.button>
</x-ui.card>
