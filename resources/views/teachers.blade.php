<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers | {{ $schoolSettings->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Teachers navigation">
            <a href="{{ route('home') }}" class="font-bold text-blue-950">{{ $schoolSettings->school_name }}</a>
            <a href="{{ route('home') }}" class="text-sm font-semibold text-blue-800 hover:underline">Back to home</a>
        </nav>
    </header>

    <main>
        <section class="bg-blue-900 px-5 py-14 text-white sm:px-8 sm:py-20">
            <div class="mx-auto max-w-6xl">
                <p class="text-sm font-bold uppercase tracking-widest text-yellow-300">Our Faculty</p>
                <h1 class="mt-3 text-4xl font-bold sm:text-5xl">Teachers</h1>
                <p class="mt-5 max-w-2xl leading-8 text-blue-100">
                    Meet the educators and academic personnel serving the school community.
                </p>
            </div>
        </section>

        <section class="px-5 py-14 sm:px-8 sm:py-20">
            <div class="mx-auto grid max-w-6xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Teacher Name', 'Position or Department'],
                    ['Teacher Name', 'Position or Department'],
                    ['Teacher Name', 'Position or Department'],
                    ['Teacher Name', 'Position or Department'],
                    ['Teacher Name', 'Position or Department'],
                    ['Teacher Name', 'Position or Department']
                ] as $teacher)
                    <article class="overflow-hidden border border-slate-200 bg-white shadow-sm">
                        <div class="flex aspect-[4/3] items-center justify-center bg-slate-200 text-slate-400" aria-label="Teacher photo placeholder">
                            <span class="text-sm font-semibold uppercase tracking-widest">Photo</span>
                        </div>
                        <div class="border-t-4 border-t-blue-800 px-6 py-5 text-center">
                            <h2 class="text-lg font-bold text-blue-950">{{ $teacher[0] }}</h2>
                            <p class="mt-1 text-sm font-semibold text-slate-500">{{ $teacher[1] }}</p>
                        </div>
                    </article>
                @endforeach
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
