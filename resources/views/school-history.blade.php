<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History of the School | {{ $schoolSettings->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8" aria-label="School history navigation">
            <a href="{{ route('home') }}" class="font-bold text-blue-950">{{ $schoolSettings->school_name }}</a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-blue-800 hover:underline">Back to home</a>
        </nav>
    </header>

    <main>
        <section class="bg-blue-900 px-5 py-14 text-white sm:px-8 sm:py-20">
            <div class="mx-auto max-w-6xl">
                <p class="text-sm font-bold uppercase tracking-widest text-yellow-300">Our Story</p>
                <h1 class="mt-3 text-4xl font-bold sm:text-5xl">History of the School</h1>
            </div>
        </section>

        <section class="px-5 py-14 sm:px-8 sm:py-20">
            <div class="mx-auto max-w-4xl rounded-lg border border-slate-200 bg-white p-8 shadow-sm sm:p-12">
                <div class="space-y-6 leading-8 text-slate-600">
                    <p>
                        Add the school's founding story and the background of its establishment here.
                    </p>
                    <p>
                        Add important milestones, achievements, growth, and developments across the years here.
                    </p>
                    <p>
                        Add a closing statement describing the school's legacy and continuing commitment to its learners and community.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 px-5 py-8 text-slate-300 sm:px-8">
        <div class="mx-auto max-w-6xl text-sm">
            &copy; {{ date('Y') }} {{ $schoolSettings->school_name }}
        </div>
    </footer>
</body>
</html>
