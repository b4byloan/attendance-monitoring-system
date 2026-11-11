<x-layout title="School Settings" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex gap-4 mb-6 text-sm">
        <span class="font-medium text-indigo-700">School Settings</span>
        <span class="text-gray-300">|</span>
        <a href="{{ route('admin.backups.index') }}" class="text-gray-500 hover:text-indigo-600">Backup &amp; Restore</a>
    </div>

    <h2 class="text-lg font-semibold text-gray-900 mb-6">School Settings</h2>

    @if (session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card title="School Information">
            @if ($settings->school_logo_path)
                <img src="{{ asset('storage/' . $settings->school_logo_path) }}" class="h-16 mb-3 rounded">
            @endif
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">School Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <input type="text" name="academic_year" value="{{ old('academic_year', $settings->academic_year) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">School Address</label>
                <textarea name="school_address" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('school_address', $settings->school_address) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Principal Name</label>
                    <input type="text" name="principal_name" value="{{ old('principal_name', $settings->principal_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                    <input type="text" name="school_contact_number" value="{{ old('school_contact_number', $settings->school_contact_number) }}" placeholder="09XXXXXXXXX"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Email</label>
                    <input type="email" name="school_email" value="{{ old('school_email', $settings->school_email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Schedule">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                    <input type="text" name="timezone" value="{{ old('timezone', $settings->timezone) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">e.g. Asia/Manila — fully active, applied instantly.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Start Time</label>
                    <input type="time" name="school_start_time" value="{{ old('school_start_time', $settings->school_start_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School End Time</label>
                    <input type="time" name="school_end_time" value="{{ old('school_end_time', $settings->school_end_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Attendance Settings">
            <x-ui.alert type="warning">
                Not yet enforced. These values save correctly, but the Scanner doesn't check them yet — no cutoff blocking, no "late" flag anywhere in the system.
            </x-ui.alert>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Start</label>
                    <input type="time" name="checkin_start_time" value="{{ old('checkin_start_time', $settings->checkin_start_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Cutoff</label>
                    <input type="time" name="checkin_cutoff_time" value="{{ old('checkin_cutoff_time', $settings->checkin_cutoff_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Start</label>
                    <input type="time" name="checkout_start_time" value="{{ old('checkout_start_time', $settings->checkout_start_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Cutoff</label>
                    <input type="time" name="checkout_cutoff_time" value="{{ old('checkout_cutoff_time', $settings->checkout_cutoff_time) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Late Threshold (minutes)</label>
                <input type="number" name="late_threshold_minutes" value="{{ old('late_threshold_minutes', $settings->late_threshold_minutes) }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </x-ui.card>

        <x-ui.card title="QR Settings">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Expiration (days)</label>
                    <input type="number" name="qr_expiration_days" value="{{ old('qr_expiration_days', $settings->qr_expiration_days) }}" min="1" placeholder="Never"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 flex items-center gap-1 text-xs text-amber-600"><x-ui.icon name="alert-triangle" class="h-3.5 w-3.5" /> Not yet enforced</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Format</label>
                    <select name="qr_format" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="png" selected>PNG</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default QR Size (px)</label>
                    <input type="number" name="qr_default_size" value="{{ old('qr_default_size', $settings->qr_default_size) }}" min="100" max="1000"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 flex items-center gap-1 text-xs text-green-700"><x-ui.icon name="circle-check" class="h-3.5 w-3.5" /> Fully active</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="SMS Settings">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMS Provider</label>
                    <select name="sms_provider" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="semaphore" selected>Semaphore</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sender Name</label>
                    <input type="text" name="sms_sender_name" value="{{ old('sms_sender_name', $settings->sms_sender_name) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                <p class="text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    @if ($apiKeyConfigured)
                        <span class="inline-flex items-center gap-1 text-green-700"><x-ui.icon name="circle-check" class="h-4 w-4" /> Configured (in .env)</span>
                    @else
                        <span class="inline-flex items-center gap-1 text-red-600"><x-ui.icon name="x" class="h-4 w-4" /> Not configured</span>
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">Never edited here — set <code>SEMAPHORE_API_KEY</code> directly in your project's <code>.env</code> file.</p>
            </div>
            <label class="flex items-center text-sm gap-2">
                <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings->sms_enabled)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Enable SMS notifications
            </label>
        </x-ui.card>

        <x-ui.button type="submit">Save Settings</x-ui.button>
    </form>

</x-layout>