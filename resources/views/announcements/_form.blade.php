@php
    $announcement = $announcement ?? null;
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
    <x-ui.form-field icon="megaphone" name="title">
        <input type="text" name="title" value="{{ old('title', $announcement->title ?? '') }}" maxlength="255"
               class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
    </x-ui.form-field>
    @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <x-ui.form-field icon="pencil" name="description">
        <textarea name="description" rows="5" maxlength="1000"
                  class="max-h-48 w-full resize-y overflow-y-auto border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">{{ old('description', $announcement->description ?? '') }}</textarea>
    </x-ui.form-field>
    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

@if ($allowSchoolWide)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
        <x-ui.form-field icon="users">
            <select name="section_id" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                <option value="">School-wide (everyone)</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(old('section_id', $announcement->section_id ?? null) == $section->id)>{{ $section->name }} only</option>
                @endforeach
            </select>
        </x-ui.form-field>
    </div>
@elseif ($lockedSection)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
        <x-ui.form-field icon="users">
            <p class="px-3 py-2 text-sm text-gray-700">{{ $lockedSection->name }} only</p>
        </x-ui.form-field>
    </div>
@else
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Audience</label>
        <x-ui.form-field icon="users">
            <select name="section_id" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(old('section_id', $announcement->section_id ?? null) == $section->id)>{{ $section->name }} only</option>
                @endforeach
            </select>
        </x-ui.form-field>
        <p class="mt-1 text-xs text-gray-500">You can post to any of your assigned sections.</p>
    </div>
@endif

@isset($announcement)
    @if ($allowSchoolWide || $sections->count() > 1)
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned', $announcement->is_pinned)) class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Pin this announcement to the top
        </label>
    @endif
@endisset

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
        <x-ui.form-field icon="calendar" name="publish_date">
            <input type="date" name="publish_date" value="{{ old('publish_date', isset($announcement) ? $announcement->publish_date->toDateString() : now()->toDateString()) }}"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>
        @error('publish_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date (optional)</label>
        <x-ui.form-field icon="calendar" name="expiration_date">
            <input type="date" name="expiration_date" value="{{ old('expiration_date', $announcement?->expiration_date?->toDateString() ?? '') }}"
                   class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
        </x-ui.form-field>
        @error('expiration_date')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
    <x-ui.form-field icon="flag">
        <select name="status" class="w-full border-0 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none focus:ring-0">
            <option value="draft" @selected(old('status', $announcement->status ?? null) === 'draft')>Draft (not visible yet)</option>
            <option value="published" @selected(old('status', $announcement->status ?? 'published') === 'published')>Published</option>
        </select>
    </x-ui.form-field>
</div>


