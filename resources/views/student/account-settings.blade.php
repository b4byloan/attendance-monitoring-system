<x-simple-layout title="Account Settings" :unread-count="$unreadCount" :recent-notifications="$recentNotifications">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Account Settings</h2>
        <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" />
    </div>

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

    <x-ui.card>
        <form method="POST" action="{{ route('student.account-settings.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <hr class="border-gray-100">
            <p class="text-sm text-gray-500">Leave the password fields blank to keep your current password.</p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" autocomplete="current-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Only required if you're setting a new password below.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">At least 8 characters.</p>
            </div>

            <x-ui.button type="submit">Save Changes</x-ui.button>
        </form>
    </x-ui.card>

</x-simple-layout>