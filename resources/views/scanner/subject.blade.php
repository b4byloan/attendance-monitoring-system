<x-simple-layout title="Class Attendance Scanner" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <x-ui.back-button :href="route(auth()->user()->dashboardRoute())" label="Back to dashboard" class="mb-4" />

    @if ($subjects->isEmpty())
        <x-ui.card>
            <x-ui.empty-state message="No subjects are assigned to you yet. Ask your administrator to assign at least one subject before taking class attendance." />
        </x-ui.card>
    @else
        <x-ui.card class="mb-4">
            <label for="subject-select" class="block text-sm font-semibold text-gray-900">Subject</label>
            <p class="mt-1 text-xs text-gray-500">Choose the subject you are currently handling. Attendance is recorded under this subject.</p>
            <select id="subject-select" class="mt-3 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select a subject before scanning —</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}{{ $subject->code ? ' ('.$subject->code.')' : '' }}</option>
                @endforeach
            </select>
            <p id="selected-subject" class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">No subject selected — scanning is disabled.</p>
        </x-ui.card>

        <x-ui.card class="mb-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🔌</span>
                <div>
                    <p class="font-semibold text-gray-900">Physical Scanner Ready</p>
                    <p class="text-xs text-gray-500">Connect a USB or Bluetooth barcode scanner, then scan — no need to tap anything first.</p>
                </div>
            </div>
            <input type="text" id="physical-scan-input"
                   inputmode="none" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                   class="opacity-0 h-0 w-0 absolute overflow-hidden" aria-hidden="true" tabindex="-1">
        </x-ui.card>

        <x-ui.card class="mb-4">
            <div id="reader" class="w-full"></div>
            <p class="text-xs text-gray-400 text-center mt-3">
                Camera needs HTTPS (or "localhost") and permission — tap the camera icon in the address bar if it's blocked.
            </p>
        </x-ui.card>

        <div id="scan-result" class="mb-4"></div>

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            const subjectSelect = document.getElementById('subject-select');
            const selectedSubjectLabel = document.getElementById('selected-subject');

            function currentSubjectId() {
                return subjectSelect.value;
            }

            function refreshSubjectLabel() {
                const option = subjectSelect.options[subjectSelect.selectedIndex];
                if (subjectSelect.value) {
                    selectedSubjectLabel.textContent = 'Recording attendance for: ' + option.textContent;
                    selectedSubjectLabel.className = 'mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800';
                } else {
                    selectedSubjectLabel.textContent = 'No subject selected — scanning is disabled.';
                    selectedSubjectLabel.className = 'mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800';
                }
            }

            subjectSelect.addEventListener('change', refreshSubjectLabel);
            refreshSubjectLabel();

            let lastScanned = null, lastScanTime = 0;

            function onScanSuccess(decodedText) {
                const now = Date.now();
                if (decodedText === lastScanned && (now - lastScanTime) < 3000) return;
                lastScanned = decodedText;
                lastScanTime = now;

                if (! currentSubjectId()) {
                    document.getElementById('scan-result').innerHTML =
                        '<div class="rounded-xl bg-amber-500 p-6 text-center text-white"><p class="text-2xl font-bold mb-1">Select a Subject</p><p class="text-sm opacity-90">Choose the subject before scanning a student QR code.</p></div>';
                    return;
                }

                verifyToken(decodedText);
            }

            function onScanFailure() {}

            new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 280, height: 280 } }, false).render(onScanSuccess, onScanFailure);

            const physicalInput = document.getElementById('physical-scan-input');
            let physicalScanTimeout = null;

            function submitPhysicalScan() {
                const value = physicalInput.value.trim();
                physicalInput.value = '';
                if (value) onScanSuccess(value);
            }

            physicalInput.addEventListener('input', function () {
                clearTimeout(physicalScanTimeout);
                physicalScanTimeout = setTimeout(submitPhysicalScan, 100);
            });

            physicalInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(physicalScanTimeout);
                    submitPhysicalScan();
                }
            });

            function refocusPhysicalInput() {
                physicalInput.focus({ preventScroll: true });
            }

            refocusPhysicalInput();
            document.addEventListener('click', function (e) {
                if (!['INPUT', 'BUTTON', 'A', 'SELECT'].includes(e.target.tagName)) {
                    refocusPhysicalInput();
                }
            });

            function photoOrInitial(s) {
                if (s.photo_url) {
                    return '<img src="' + s.photo_url + '" class="w-16 h-16 rounded-full object-cover mx-auto mb-2">';
                }
                const initials = s.full_name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
                return '<div class="w-16 h-16 rounded-full bg-white/30 flex items-center justify-center text-xl font-bold mx-auto mb-2">' + initials + '</div>';
            }

            function display(data) {
                const el = document.getElementById('scan-result');

                if (!data.found) {
                    el.innerHTML = '<div class="rounded-xl bg-red-600 p-6 text-center text-white"><p class="text-2xl font-bold mb-1">✕ Not Recognized</p><p class="text-sm opacity-90">' + (data.message || 'This QR code does not match any student.') + '</p></div>';
                    return;
                }

                const s = data.student;

                if (!data.active) {
                    el.innerHTML = '<div class="rounded-xl bg-amber-500 p-6 text-center text-white">' + photoOrInitial(s) +
                        '<p class="text-2xl font-bold">' + s.full_name + '</p>' +
                        '<p class="mt-2 font-semibold">Status: ' + s.status.toUpperCase() + ' — attendance not recorded</p></div>';
                    return;
                }

                if (data.attendance === 'already_recorded') {
                    el.innerHTML = '<div class="rounded-xl bg-gray-500 p-6 text-center text-white">' + photoOrInitial(s) +
                        '<p class="text-2xl font-bold">' + s.full_name + '</p>' +
                        '<p class="text-sm opacity-90">' + (s.section || '') + '</p>' +
                        '<p class="mt-2 font-semibold">Already recorded for ' + data.subject + ' today</p></div>';
                    return;
                }

                el.innerHTML = '<div class="rounded-xl bg-emerald-600 p-6 text-center text-white">' + photoOrInitial(s) +
                    '<p class="text-2xl font-bold">' + s.full_name + '</p>' +
                    '<p class="text-sm opacity-90">' + (s.section || '') + '</p>' +
                    '<p class="mt-3 text-3xl font-extrabold">✓ Present</p>' +
                    '<p class="text-sm mt-1 opacity-90">' + data.subject + '</p>' +
                    (data.time ? '<p class="text-lg mt-1">' + data.time + '</p>' : '') + '</div>';
            }

            async function verifyToken(token) {
                document.getElementById('scan-result').innerHTML =
                    '<div class="rounded-xl bg-gray-400 p-6 text-center text-white"><p class="text-xl font-bold">Processing scan…</p></div>';

                try {
                    const response = await fetch("{{ route('teacher-scanner.verify') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ token: token, subject_id: currentSubjectId() }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        document.getElementById('scan-result').innerHTML =
                            '<div class="rounded-xl bg-red-600 p-6 text-center text-white"><p class="text-2xl font-bold mb-1">✕ Scan Not Saved</p><p class="text-sm opacity-90">' + (data.message || 'The server could not save this scan.') + '</p></div>';
                        return;
                    }

                    display(data);
                } catch (e) {
                    document.getElementById('scan-result').innerHTML =
                        '<div class="rounded-xl bg-red-600 p-6 text-center text-white"><p class="text-2xl font-bold mb-1">✕ Connection Problem</p><p class="text-sm opacity-90">Check the connection and try again.</p></div>';
                }
            }
        </script>
    @endif

</x-simple-layout>
