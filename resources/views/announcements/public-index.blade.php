<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | {{ $schoolSettings->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Announcements navigation">
            <a href="{{ route('home') }}" class="font-bold text-blue-950">
                {{ $schoolSettings->school_name }}
            </a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-blue-800 hover:underline">
                Back to home
            </a>
        </nav>
    </header>

    <main class="mx-auto max-w-5xl px-5 py-12 sm:px-8 sm:py-16">
        <p class="text-sm font-bold uppercase tracking-widest text-blue-800">School Updates</p>
        <h1 class="mt-2 text-3xl font-bold text-blue-950 sm:text-4xl">Announcements</h1>

        <div class="mt-8 space-y-5">
            @forelse ($announcements as $announcement)
                <article class="{{ $announcement->is_pinned ? 'border-t-4 border-t-yellow-400' : 'border-t-4 border-t-blue-800' }} bg-white p-6 shadow-sm sm:p-8">
                    @if ($announcement->is_pinned)
                        <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-yellow-800">
                            Pinned
                        </span>
                    @endif

                    <h2 class="mt-4 text-2xl font-bold text-blue-950">{{ $announcement->title }}</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $announcement->publish_date->format('F j, Y') }}
                        @if ($announcement->expiration_date)
                            <span aria-hidden="true">&middot;</span>
                            Valid until {{ $announcement->expiration_date->format('F j, Y') }}
                        @endif
                    </p>
                    <div class="mt-5 max-w-4xl whitespace-pre-line leading-8 text-slate-700">
                        {{ $announcement->description }}
                    </div>
                </article>
            @empty
                <div class="border border-slate-200 bg-white p-8 text-center text-slate-600 shadow-sm">
                    There are no active announcements at this time.
                </div>
            @endforelse
        </div>
    </main>
</body>
</html>
