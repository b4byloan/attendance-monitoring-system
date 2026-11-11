<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login - {{ $schoolSettings->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-emerald-950 text-slate-800 antialiased lg:bg-slate-50">
    <div class="relative grid min-h-screen lg:grid-cols-[1fr_480px]">
        <section class="fixed inset-0 z-0 overflow-hidden lg:relative lg:inset-auto lg:z-auto lg:flex lg:flex-col lg:justify-between">
            <x-auth.carousel />

            <div class="relative hidden p-12 lg:block">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white">
                    @if ($schoolSettings->school_logo_path)
                        <img src="{{ asset('storage/' . $schoolSettings->school_logo_path) }}" alt="" class="h-14 w-14 rounded-full bg-white object-contain p-1">
                    @else
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/30">
                            <x-ui.icon name="school" class="h-7 w-7" />
                        </span>
                    @endif
                    <span>
                        <span class="block text-lg font-bold">{{ $schoolSettings->school_name }}</span>
                        <span class="text-xs font-semibold tracking-widest text-emerald-100">Attendance Monitoring System</span>
                    </span>
                </a>
            </div>

            <div class="relative hidden px-12 pb-14 lg:block">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-yellow-300">School Attendance Portal</p>
                <h1 class="mt-4 max-w-xl text-4xl font-bold leading-tight text-white">Stay connected to every school day.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-emerald-50">Access attendance records, school announcements, and the information you need in one secure portal.</p>
            </div>
        </section>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-8">
            <div class="w-full max-w-sm rounded-2xl bg-white/95 p-6 shadow-2xl ring-1 ring-white/60 backdrop-blur sm:p-8 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0 lg:backdrop-blur-none">
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 lg:hidden">
                    @if ($schoolSettings->school_logo_path)
                        <img src="{{ asset('storage/' . $schoolSettings->school_logo_path) }}" alt="" class="h-12 w-12 rounded-full border border-slate-200 bg-white object-contain">
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-700 text-white">
                            <x-ui.icon name="school" class="h-6 w-6" />
                        </span>
                    @endif
                    <span class="font-bold text-emerald-950">{{ $schoolSettings->school_name }}</span>
                </a>

                <p class="text-sm font-bold uppercase tracking-[0.16em] text-emerald-700">Welcome back</p>
                <h2 class="mt-2 text-3xl font-bold text-emerald-950">Sign in to your account</h2>
                <p class="mt-2 text-sm text-slate-500">Use the email and password linked to your school portal account.</p>

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <x-ui.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                        Remember me on this device
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-emerald-700 px-5 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                        Sign in
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-slate-600">
                    Need an account?
                    <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:text-emerald-900">Register as parent or student</a>
                </p>
                <a href="{{ route('home') }}" class="mt-5 flex items-center justify-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-emerald-700">
                    <x-ui.icon name="arrow-left" class="h-4 w-4" />
                    Back to school website
                </a>
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
