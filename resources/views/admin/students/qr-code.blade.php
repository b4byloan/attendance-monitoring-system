<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code - {{ $student->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow text-center">
        <h1 class="text-xl font-bold mb-1">{{ $student->full_name }}</h1>
        <p class="text-sm text-gray-500 mb-4">LRN: {{ $student->lrn }}</p>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm no-print">{{ session('success') }}</div>
        @endif

        <img src="{{ $dataUri }}" alt="QR code for {{ $student->full_name }}" class="mx-auto mb-4">

        <div class="flex justify-center gap-2 no-print">
            <a href="{{ route('admin.students.qr-code.download', $student) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Download
            </a>
            <button onclick="window.print()" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
                Print
            </button>
            <form method="POST" action="{{ route('admin.students.qr-code.regenerate', $student) }}"
                  onsubmit="return confirm('This will invalidate the current QR code. Continue?');">
                @csrf
                @method('PATCH')
                <button class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">Regenerate</button>
            </form>
        </div>

        <a href="{{ route('admin.students.show', $student) }}" class="block mt-4 text-sm text-gray-500 no-print">
            ← Back to student
        </a>
    </div>
</body>
</html>