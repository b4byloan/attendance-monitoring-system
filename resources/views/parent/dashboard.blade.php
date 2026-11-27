<x-simple-layout title="Parent Dashboard" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-4">
            <x-ui.avatar :name="auth()->user()->name" size="lg" />
            <div>
                <p class="text-sm text-gray-500">Hello,</p>
                <h2 class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</h2>
            </div>
        </div>
        <x-ui.button href="{{ route('parent.link-student.index') }}" variant="secondary" size="sm">Link your account to a student</x-ui.button>
    </div>

    @if ($students->count() > 1)
        <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
            @foreach ($students as $s)
                <a href="{{ route('parent.students.dashboard', $s) }}"
                   class="flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap {{ $s->id === $student->id ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}">
                    <x-ui.avatar :name="$s->full_name" :photo="$s->photo_path" size="xs" />
                    {{ $s->full_name }}
                </a>
            @endforeach
        </div>
    @endif

    <x-ui.card class="mb-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
            <div class="flex items-center gap-4">
                <x-ui.avatar :name="$student->full_name" :photo="$student->photo_path" size="lg" />
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $student->full_name }}</h3>
                    <p class="text-sm text-gray-500">{{ $student->section->grade_level ?? '—' }} · {{ $student->section->name ?? 'No section' }}</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <p class="text-sm text-gray-500">LRN: <span class="font-mono text-gray-700">{{ $student->lrn }}</span></p>
                        <x-ui.copy-button :value="$student->lrn" />
                    </div>
                </div>
            </div>
            @if ($todayCheckIn && $todayCheckOut)
                <x-ui.badge color="green">Checked Out</x-ui.badge>
            @elseif ($todayCheckIn)
                <x-ui.badge color="blue">Checked In</x-ui.badge>
            @else
                <x-ui.badge color="gray">Not Checked In Yet</x-ui.badge>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Check-In Time</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $todayCheckIn ? $todayCheckIn->scanned_at->format('h:i A') : '—' }}
                </p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Check-Out Time</p>
                <p class="text-lg font-semibold text-gray-900 mt-1">
                    {{ $todayCheckOut ? $todayCheckOut->scanned_at->format('h:i A') : '—' }}
                </p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-base font-semibold text-gray-900">Recent Attendance</h3>
            <a href="{{ route('parent.students.attendance', $student) }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">Full history <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
        </div>
        @forelse ($recent as $record)
            <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 border-b border-gray-100 py-2.5 text-sm last:border-0">
                <span class="min-w-0 break-words">{{ $record->attendance_date->format('D, M j') }} — {{ $record->type === 'check_in' ? 'Check-In' : 'Check-Out' }}</span>
                <span class="whitespace-nowrap tabular-nums text-gray-500">{{ $record->scanned_at->format('h:i A') }}</span>
            </div>
        @empty
            <x-ui.empty-state message="No attendance recorded yet." />
        @endforelse
    </x-ui.card>

    @php
        $announcementData = $announcements->map(fn ($announcement) => [
            'title' => $announcement->title,
            'date' => $announcement->publish_date->format('F j, Y'),
            'expires' => $announcement->expiration_date?->format('F j, Y'),
            'audience' => $announcement->isSchoolWide() ? 'School-wide' : ($announcement->section->name ?? 'Section'),
            'author' => $announcement->author->name ?? 'Unknown',
            'pinned' => (bool) $announcement->is_pinned,
            'description' => $announcement->description,
        ])->values();
    @endphp

    <x-ui.card title="Announcements">
        @forelse ($announcements as $a)
            <div class="flex flex-wrap items-start justify-between gap-2 border-b border-gray-100 py-2.5 text-sm last:border-0">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">
                        @if ($a->is_pinned)<x-ui.icon name="pin" class="mr-1 inline h-3.5 w-3.5 align-text-bottom text-amber-500" />@endif
                        <span class="break-words">{{ $a->title }}</span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $a->publish_date->format('M j, Y') }}</p>
                </div>
                <button type="button" data-announcement-index="{{ $loop->index }}"
                        class="shrink-0 text-sm font-medium text-indigo-600 hover:underline">
                    View more
                </button>
            </div>
        @empty
            <x-ui.empty-state message="No announcements right now." />
        @endforelse

        <a href="{{ route('parent.announcements') }}" class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">View all <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" /></a>
    @if ($announcementData->isNotEmpty())
        <dialog id="announcement-modal" class="w-full max-w-xl rounded-lg p-0 shadow-xl backdrop:bg-black/40">
            <div class="relative max-h-[85vh] overflow-y-auto p-6">
                <button type="button" onclick="document.getElementById('announcement-modal').close()" aria-label="Close dialog"
                        class="absolute left-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <x-ui.icon name="x" class="h-4 w-4" />
                </button>

                <h3 id="announcement-modal-title" class="mb-1 pl-8 pr-2 text-lg font-bold text-gray-900"></h3>
                <p id="announcement-modal-meta" class="mb-4 pl-8 text-xs text-gray-500"></p>

                <div id="announcement-modal-body" class="whitespace-pre-line break-words text-sm leading-relaxed text-gray-700"></div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4">
                    <x-ui.button type="button" variant="secondary" size="sm" id="announcement-prev">← Previous</x-ui.button>
                    <span id="announcement-counter" class="text-xs text-gray-500"></span>
                    <x-ui.button type="button" variant="secondary" size="sm" id="announcement-next">Next →</x-ui.button>
                </div>
            </div>
        </dialog>

        @push('scripts')
            <script>
                (function () {
                    const announcements = @json($announcementData);
                    const dialog = document.getElementById('announcement-modal');
                    const title = document.getElementById('announcement-modal-title');
                    const meta = document.getElementById('announcement-modal-meta');
                    const body = document.getElementById('announcement-modal-body');
                    const counter = document.getElementById('announcement-counter');
                    const prev = document.getElementById('announcement-prev');
                    const next = document.getElementById('announcement-next');
                    let index = 0;

                    function render() {
                        const announcement = announcements[index];

                        title.textContent = (announcement.pinned ? 'Pinned · ' : '') + announcement.title;

                        let metaText = announcement.date + ' · ' + announcement.audience + ' · By ' + announcement.author;
                        if (announcement.expires) {
                            metaText += ' · Expires ' + announcement.expires;
                        }

                        meta.textContent = metaText;
                        body.textContent = announcement.description;
                        counter.textContent = (index + 1) + ' of ' + announcements.length;

                        const single = announcements.length < 2;
                        prev.disabled = single;
                        next.disabled = single;
                        prev.classList.toggle('opacity-50', single);
                        next.classList.toggle('opacity-50', single);
                    }

                    function open(targetIndex) {
                        index = (targetIndex + announcements.length) % announcements.length;
                        render();
                        dialog.showModal();
                    }

                    document.querySelectorAll('[data-announcement-index]').forEach(function (button) {
                        button.addEventListener('click', function () {
                            open(parseInt(button.dataset.announcementIndex, 10));
                        });
                    });

                    prev.addEventListener('click', function () { open(index - 1); });
                    next.addEventListener('click', function () { open(index + 1); });

                    dialog.addEventListener('keydown', function (event) {
                        if (event.key === 'ArrowLeft') { open(index - 1); }
                        if (event.key === 'ArrowRight') { open(index + 1); }
                    });
                })();
            </script>
        @endpush
    @endif


    </x-ui.card>

</x-simple-layout>