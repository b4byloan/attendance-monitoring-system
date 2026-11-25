<x-mail::message>
# Attendance Recorded

Hello {{ $guardianName }},

{{ $studentName }} has a recorded school attendance transaction.

<x-mail::table>
| Detail | Information |
| :--- | :--- |
| Student | {{ $studentName }} |
| Date | {{ $date }} |
| Time | {{ $time }} |
| Subject | {{ $subjectName ?? 'School gate attendance' }} |
| Status | {{ $status }} |
@if ($recordedBy)
| Recorded by | {{ $recordedBy }} |
@endif
</x-mail::table>

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
