<x-simple-layout title="Scanner" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
        <span id="connection-badge" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700">Checking…</span>
        <div class="flex gap-4 text-sm text-gray-600">
            <span>Pending: <strong id="pending-count">0</strong></span>
            <span>Last synced: <strong id="last-sync-time">Checking…</strong></span>
        </div>
    </div>
    <div id="sync-status" class="text-xs text-gray-500 mb-4"></div>

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
        const SCANNER_USER_ID = {{ auth()->id() }};

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js');
        }

        const DB_NAME = 'scanner_offline_db';
        const DB_VERSION = 2;
        const STORE_DIRECTORY = 'student_directory';
        const STORE_PENDING = 'pending_attendance';
        const STORE_META = 'sync_meta';

        function openDb() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(DB_NAME, DB_VERSION);
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains(STORE_DIRECTORY)) db.createObjectStore(STORE_DIRECTORY, { keyPath: 'qr_code' });
                    if (!db.objectStoreNames.contains(STORE_PENDING)) db.createObjectStore(STORE_PENDING, { keyPath: 'id', autoIncrement: true });
                    if (!db.objectStoreNames.contains(STORE_META)) db.createObjectStore(STORE_META, { keyPath: 'key' });
                };
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        }

        async function refreshDirectory() {
            try {
                const response = await fetch("{{ route('scanner.directory') }}");
                if (!response.ok) return;
                const students = await response.json();
                const db = await openDb();
                const tx = db.transaction(STORE_DIRECTORY, 'readwrite');
                const store = tx.objectStore(STORE_DIRECTORY);
                store.clear();
                students.forEach((s) => store.put(s));
            } catch (e) {}
        }

        function lookupOffline(token) {
            return new Promise(async (resolve) => {
                const db = await openDb();
                const request = db.transaction(STORE_DIRECTORY, 'readonly').objectStore(STORE_DIRECTORY).get(token);
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => resolve(null);
            });
        }

        function todayString() {
            const now = new Date();
            return now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        }

        async function getAllPending() {
            const db = await openDb();
            return new Promise((resolve) => {
                const request = db.transaction(STORE_PENDING, 'readonly').objectStore(STORE_PENDING).getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => resolve([]);
            });
        }

        async function recordOfflineAttendance(qrCode) {
            const now = new Date();
            const allPending = await getAllPending();

            const recentSameStudent = allPending.some((r) =>
                r.qr_code === qrCode && (now - new Date(r.scanned_at)) < 2 * 60 * 1000
            );
            if (recentSameStudent) {
                return { status: 'recent_duplicate' };
            }

            const today = todayString();
            const todaysForStudent = allPending.filter((r) => r.qr_code === qrCode && r.scanned_date === today && r.sync_status !== 'resolved');
            const hasCheckIn = todaysForStudent.some((r) => r.type === 'check_in');
            const hasCheckOut = todaysForStudent.some((r) => r.type === 'check_out');

            if (hasCheckIn && hasCheckOut) {
                return { status: 'already_completed' };
            }

            const type = hasCheckIn ? 'check_out' : 'check_in';
            const record = { qr_code: qrCode, type, scanned_at: now.toISOString(), scanned_date: today, scanner_user_id: SCANNER_USER_ID, sync_status: 'pending' };

            const db = await openDb();
            db.transaction(STORE_PENDING, 'readwrite').objectStore(STORE_PENDING).add(record);

            return { status: type, time: now };
        }

        async function markRecordStatus(id, status) {
            const db = await openDb();
            const store = db.transaction(STORE_PENDING, 'readwrite').objectStore(STORE_PENDING);
            return new Promise((resolve) => {
                const getRequest = store.get(id);
                getRequest.onsuccess = () => {
                    const record = getRequest.result;
                    if (record) { record.sync_status = status; store.put(record); }
                    resolve();
                };
                getRequest.onerror = () => resolve();
            });
        }

        async function updatePendingCount() {
            const allPending = await getAllPending();
            document.getElementById('pending-count').textContent = allPending.filter((r) => r.sync_status === 'pending').length;
        }

        async function setLastSyncTime(date) {
            const db = await openDb();
            db.transaction(STORE_META, 'readwrite').objectStore(STORE_META).put({ key: 'last_sync_time', value: date.toISOString() });
        }

        async function updateSyncStatusDisplay() {
            const db = await openDb();
            const request = db.transaction(STORE_META, 'readonly').objectStore(STORE_META).get('last_sync_time');
            request.onsuccess = () => {
                const value = request.result ? request.result.value : null;
                document.getElementById('last-sync-time').textContent = value ? new Date(value).toLocaleString() : 'Never synced yet';
            };
            request.onerror = () => { document.getElementById('last-sync-time').textContent = 'Never synced yet'; };
        }

        let syncInProgress = false;

        async function syncPendingRecords() {
            if (syncInProgress || !navigator.onLine) return;
            syncInProgress = true;

            const toSync = (await getAllPending()).filter((r) => r.sync_status === 'pending');
            if (toSync.length === 0) { syncInProgress = false; return; }

            let synced = 0, resolved = 0, failed = 0;
            for (let i = 0; i < toSync.length; i++) {
                document.getElementById('sync-status').textContent = 'Syncing record ' + (i + 1) + ' of ' + toSync.length + '…';
                const record = toSync[i];
                try {
                    const response = await fetch("{{ route('scanner.sync') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ qr_code: record.qr_code, scanned_at: record.scanned_at, attendance_date: record.scanned_date }),
                    });
                    const data = await response.json();
                    await markRecordStatus(record.id, data.status === 'synced' ? 'synced' : 'resolved');
                    data.status === 'synced' ? synced++ : resolved++;
                } catch (e) { failed++; }
            }

            if (synced + resolved > 0) await setLastSyncTime(new Date());
            await updatePendingCount();
            await updateSyncStatusDisplay();

            const parts = [];
            if (synced) parts.push(synced + ' synced');
            if (resolved) parts.push(resolved + ' resolved');
            if (failed) parts.push(failed + ' still pending');
            document.getElementById('sync-status').textContent = parts.length ? parts.join(', ') + '.' : '';

            syncInProgress = false;
        }

        function updateConnectionBadge() {
            const badge = document.getElementById('connection-badge');
            if (navigator.onLine) {
                badge.textContent = 'Online';
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
            } else {
                badge.textContent = 'Offline Mode';
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            }
        }

        window.addEventListener('online', () => { updateConnectionBadge(); refreshDirectory(); syncPendingRecords(); });
        window.addEventListener('offline', updateConnectionBadge);
        updateConnectionBadge();
        refreshDirectory();
        updatePendingCount();
        updateSyncStatusDisplay();
        syncPendingRecords();
        setInterval(syncPendingRecords, 30000);

        let lastScanned = null, lastScanTime = 0;

        function onScanSuccess(decodedText) {
            const now = Date.now();
            if (decodedText === lastScanned && (now - lastScanTime) < 3000) return;
            lastScanned = decodedText;
            lastScanTime = now;
            verifyToken(decodedText);
        }
        function onScanFailure() {}

        new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 280, height: 280 } }, false).render(onScanSuccess, onScanFailure);

        async function verifyToken(token) {
            if (navigator.onLine) {
                try {
                    const response = await fetch("{{ route('scanner.verify') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ token }),
                    });
                    displayResult(await response.json(), false);
                    return;
                } catch (e) {}
            }

            const student = await lookupOffline(token);
            if (!student) { displayResult({ found: false }, true); return; }
            if (student.status !== 'active') { displayResult({ found: true, active: false, student }, true); return; }

            const outcome = await recordOfflineAttendance(token);
            await updatePendingCount();
            displayResult({
                found: true, active: true, attendance: outcome.status,
                time: outcome.time ? outcome.time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : null,
                student,
            }, true);
        }

        function photoOrInitial(s) {
            if (s.photo_url) {
                return '<img src="' + s.photo_url + '" class="w-16 h-16 rounded-full object-cover mx-auto mb-2">';
            }
            const initials = s.full_name.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
            return '<div class="w-16 h-16 rounded-full bg-white/30 flex items-center justify-center text-xl font-bold mx-auto mb-2">' + initials + '</div>';
        }

        function displayResult(data, isOffline) {
            const el = document.getElementById("scan-result");
            const offlineTag = isOffline ? '<p class="text-sm opacity-80 mt-1">Recorded offline — will sync later</p>' : '';

            if (!data.found) {
                el.innerHTML = '<div class="bg-red-600 text-white rounded-xl p-6 text-center"><p class="text-2xl font-bold mb-1">✕ Not Recognized</p><p class="text-sm opacity-90">' + (isOffline ? 'Not in the offline copy — reconnect if this student is new.' : 'This QR code does not match any student.') + '</p></div>';
                return;
            }

            const s = data.student;
            const lrnLine = s.lrn ? '<p class="text-sm opacity-90">LRN: ' + s.lrn + '</p>' : (isOffline ? '<p class="text-xs opacity-70">LRN available when online</p>' : '');

            if (!data.active) {
                el.innerHTML = '<div class="bg-amber-500 text-white rounded-xl p-6 text-center">' + photoOrInitial(s) +
                    '<p class="text-2xl font-bold">' + s.full_name + '</p>' + lrnLine +
                    '<p class="text-sm opacity-90">' + (s.section ?? '') + '</p>' +
                    '<p class="mt-2 font-semibold">Status: ' + s.status.toUpperCase() + ' — not a valid check-in</p>' + offlineTag + '</div>';
                return;
            }

            if (data.attendance === 'recent_duplicate') {
                el.innerHTML = '<div class="bg-gray-500 text-white rounded-xl p-6 text-center">' + photoOrInitial(s) +
                    '<p class="text-2xl font-bold">' + s.full_name + '</p>' +
                    '<p class="mt-2 font-semibold">Already scanned moments ago — no action taken</p></div>';
                return;
            }

            if (data.attendance === 'already_completed') {
                el.innerHTML = '<div class="bg-gray-500 text-white rounded-xl p-6 text-center">' + photoOrInitial(s) +
                    '<p class="text-2xl font-bold">' + s.full_name + '</p>' +
                    '<p class="mt-2 font-semibold">Attendance already completed for today</p>' + offlineTag + '</div>';
                return;
            }

            if (data.attendance === 'error') {
                el.innerHTML = '<div class="bg-red-600 text-white rounded-xl p-6 text-center"><p class="text-xl font-bold">Could Not Save</p><p class="text-sm opacity-90">Please scan again</p></div>';
                return;
            }

            const isCheckIn = data.attendance === 'check_in';
            const bg = isCheckIn ? 'bg-green-600' : 'bg-blue-600';
            const label = isCheckIn ? 'Checked In' : 'Checked Out';

            el.innerHTML = '<div class="' + bg + ' text-white rounded-xl p-6 text-center">' + photoOrInitial(s) +
                '<p class="text-2xl font-bold">' + s.full_name + '</p>' + lrnLine +
                '<p class="text-sm opacity-90">' + (s.section ?? '') + '</p>' +
                '<p class="mt-3 text-3xl font-extrabold">✓ ' + label + '</p>' +
                (data.time ? '<p class="text-lg mt-1">' + data.time + '</p>' : '') + offlineTag + '</div>';
        }
    </script>

</x-simple-layout>