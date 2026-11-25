<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your QR Code - {{ $student->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 p-6 sm:p-8">
    <main class="mx-auto max-w-md">
        <div class="mb-4">
            <x-ui.back-button :href="route('student.dashboard')" label="Back to dashboard" />
        </div>

        <section class="rounded-lg bg-white p-6 text-center shadow">
            <h1 class="mb-1 text-xl font-bold">{{ $student->full_name }}</h1>
            <p class="mb-4 text-sm text-gray-500">LRN: {{ $student->lrn }}</p>
            <img src="{{ $dataUri }}" alt="QR code for {{ $student->full_name }}" class="mx-auto">
        </section>
    </main>
</body>
</html>
