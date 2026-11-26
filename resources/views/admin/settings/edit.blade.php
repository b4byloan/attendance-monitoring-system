<x-layout title="School Settings" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <h2 class="text-lg font-semibold text-gray-900 mb-6">School Settings</h2>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card title="School Information">
            @if ($settings->school_logo_path)
                <img src="{{ asset('storage/' . $settings->school_logo_path) }}" class="h-16 mb-3 rounded">
            @endif
            <div class="mb-4">
                <label for="school-logo" class="block text-sm font-medium text-gray-700 mb-1">School Logo</label>
                <input id="school-logo" type="file" name="logo" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm @error('logo') border-red-400 @enderror">
                <p class="mt-1 text-xs text-gray-500">Allowed formats: JPG, JPEG, PNG, GIF, BMP, WebP. Maximum size: 10 MB.</p>
                @error('logo')
                    <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                    <x-ui.form-field icon="school">
                        <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="academic-year-start" class="block text-xs text-gray-500 mb-1">Start year</label>
                            <x-ui.form-field icon="calendar">
                                <input id="academic-year-start" type="number" name="academic_year_start" value="{{ old('academic_year_start', $academicYearStart) }}" min="1000" max="9999" step="1" required
                                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            </x-ui.form-field>
                            @error('academic_year_start')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="academic-year-end" class="block text-xs text-gray-500 mb-1">End year</label>
                            <x-ui.form-field icon="calendar">
                                <input id="academic-year-end" type="number" name="academic_year_end" value="{{ old('academic_year_end', $academicYearEnd) }}" min="1000" max="9999" step="1" required
                                       class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            </x-ui.form-field>
                            @error('academic_year_end')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">School Address</label>
                <x-ui.form-field icon="pin">
                    <textarea name="school_address" rows="2"
                              class="max-h-32 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('school_address', $settings->school_address) }}</textarea>
                </x-ui.form-field>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Principal Name</label>
                    <x-ui.form-field icon="user">
                        <input type="text" name="principal_name" value="{{ old('principal_name', $settings->principal_name) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                    <x-ui.form-field icon="phone">
                        <input type="text" name="school_contact_number" value="{{ old('school_contact_number', $settings->school_contact_number) }}" placeholder="09XXXXXXXXX"
                               inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09"
                               data-digits-only data-phone-number data-max-digits="11"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Email</label>
                    <x-ui.form-field icon="mail">
                        <input type="email" name="school_email" value="{{ old('school_email', $settings->school_email) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Schedule">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                    <x-ui.form-field icon="settings">
                        <select name="timezone"
                                class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            @php
                                $currentTimezone = old('timezone', $settings->timezone);
                                $timezones = ['Asia/Manila', 'Asia/Singapore', 'Asia/Tokyo', 'Asia/Seoul', 'Asia/Hong_Kong', 'Australia/Sydney', 'UTC'];
                            @endphp
                            @if ($currentTimezone && ! in_array($currentTimezone, $timezones, true))
                                <option value="{{ $currentTimezone }}" selected>{{ $currentTimezone }}</option>
                            @endif
                            @foreach ($timezones as $timezoneOption)
                                <option value="{{ $timezoneOption }}" @selected($currentTimezone === $timezoneOption)>{{ $timezoneOption }}</option>
                            @endforeach
                        </select>
                    </x-ui.form-field>
                    <p class="text-xs text-gray-500 mt-1">Applied instantly across the system.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School Start Time</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="school_start_time" value="{{ old('school_start_time', $settings->school_start_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">School End Time</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="school_end_time" value="{{ old('school_end_time', $settings->school_end_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Attendance Settings">
            <x-ui.notice type="warning">
                Not yet enforced. These values save correctly, but the Scanner doesn't check them yet — no cutoff blocking, no "late" flag anywhere in the system.
            </x-ui.notice>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Start</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="checkin_start_time" value="{{ old('checkin_start_time', $settings->checkin_start_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Cutoff</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="checkin_cutoff_time" value="{{ old('checkin_cutoff_time', $settings->checkin_cutoff_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Start</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="checkout_start_time" value="{{ old('checkout_start_time', $settings->checkout_start_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Cutoff</label>
                    <x-ui.form-field icon="calendar">
                        <input type="time" name="checkout_cutoff_time" value="{{ old('checkout_cutoff_time', $settings->checkout_cutoff_time) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Late Threshold (minutes)</label>
                <x-ui.form-field icon="hash">
                    <input type="number" name="late_threshold_minutes" value="{{ old('late_threshold_minutes', $settings->late_threshold_minutes) }}" min="0"
                           class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                </x-ui.form-field>
            </div>
        </x-ui.card>

        <x-ui.card title="QR Settings">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Format</label>
                    <x-ui.form-field icon="qr-code">
                        <select name="qr_format" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            <option value="png" selected>PNG</option>
                        </select>
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default QR Size (px)</label>
                    <x-ui.form-field icon="qr-code">
                        <input type="number" name="qr_default_size" value="{{ old('qr_default_size', $settings->qr_default_size) }}" min="100" max="1000"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                    <p class="mt-1 flex items-center gap-1 text-xs text-green-700"><x-ui.icon name="circle-check" class="h-3.5 w-3.5" /> Fully active</p>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500">Student QR codes are permanent — they never expire and cannot be regenerated.</p>
        </x-ui.card>

        <x-ui.card title="SMS Settings">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMS Provider</label>
                    <x-ui.form-field icon="phone">
                        <select name="sms_provider" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                            <option value="semaphore" selected>Semaphore</option>
                        </select>
                    </x-ui.form-field>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sender Name</label>
                    <x-ui.form-field icon="megaphone">
                        <input type="text" name="sms_sender_name" value="{{ old('sms_sender_name', $settings->sms_sender_name) }}"
                               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                    </x-ui.form-field>
                </div>
            </div>
            {{-- <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                <p class="text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    @if ($apiKeyConfigured)
                        <span class="inline-flex items-center gap-1 text-green-700"><x-ui.icon name="circle-check" class="h-4 w-4" /> Configured (in .env)</span>
                    @else
                        <span class="inline-flex items-center gap-1 text-red-600"><x-ui.icon name="x" class="h-4 w-4" /> Not configured</span>
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-1">Never edited here — set <code>SEMAPHORE_API_KEY</code> directly in your project's <code>.env</code> file.</p>
            </div> --}}
            <label class="flex items-center text-sm gap-2">
                <input type="checkbox" name="sms_enabled" value="1" @checked(old('sms_enabled', $settings->sms_enabled)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Enable SMS notifications
            </label>
        </x-ui.card>

        <x-ui.button type="submit">Save Settings</x-ui.button>
    </form>

</x-layout>