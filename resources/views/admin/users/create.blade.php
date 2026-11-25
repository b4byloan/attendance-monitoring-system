<x-layout title="Add User" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="max-w-xl mx-auto">
        <x-ui.back-button :href="route('admin.users.index')" label="Back to users" class="mb-4" />

        <x-ui.card title="Add User">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <x-ui.password-input name="password" :required="true" class="@error('password') border-red-400 @enderror" />
                    @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">At least 8 characters.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <x-ui.password-input name="password_confirmation" :required="true" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-400 @enderror">
                        @foreach (\App\Models\User::roleOptions() as $value => $label)
                            <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Sections (Teachers / Sub-Admins)</label>
                    <div class="max-h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-3">
                        @foreach ($sections as $section)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="section_ids[]" value="{{ $section->id }}"
                                       @checked(in_array($section->id, old('section_ids', []))) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $section->name }}
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Only applies to Teacher and Sub-Admin accounts. A teacher can be assigned to multiple sections.</p>
                </div>
                <div class="flex gap-2 pt-2">
                    <x-ui.button type="submit">Create Account</x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('admin.users.index') }}">Cancel</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

</x-layout>
