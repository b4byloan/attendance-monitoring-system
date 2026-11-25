<x-simple-layout title="Parent Dashboard">
    <x-ui.card>
        <x-ui.empty-state message="Your account isn't linked to any student yet. Send a link request — the student can approve it from their portal.">
            <x-slot:action>
                <x-ui.button href="{{ route('parent.link-student.index') }}">Link a student</x-ui.button>
            </x-slot:action>
        </x-ui.empty-state>
    </x-ui.card>
</x-simple-layout>