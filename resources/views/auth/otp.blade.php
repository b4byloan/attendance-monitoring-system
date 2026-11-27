<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email - {{ $schoolSettings->school_name }}</title>
    @vite('resources/css/app.css')
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
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-yellow-300">One more step</p>
                <h1 class="mt-4 max-w-xl text-4xl font-bold leading-tight text-white">Confirm your email to finish signing up.</h1>
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

                <p class="text-sm font-bold uppercase tracking-[0.16em] text-emerald-700">Verify your email</p>
                <h2 class="mt-2 text-3xl font-bold text-emerald-950">Enter your one-time password</h2>
                <p class="mt-2 text-sm text-slate-500">
                    We emailed a {{ \App\Services\OtpService::CODE_LENGTH }}-digit code to
                    <span class="font-semibold text-emerald-900">{{ $email }}</span>.
                    @if ($expiresAt)
                        It expires at {{ \Illuminate\Support\Carbon::parse($expiresAt)->setTimezone(config('app.timezone'))->format('g:i A') }}.
                    @endif
                </p>

                @if (session('status'))
                    <div class="mt-6 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                        <x-ui.icon name="circle-check" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <x-ui.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif


                <form method="POST" action="{{ route('otp.verify') }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="code" class="block text-sm font-semibold text-slate-700">Verification code</label>
                        <x-auth.field icon="lock" name="code">
                            <input id="code" type="text" name="code" value="" required autofocus inputmode="numeric"
                                   autocomplete="one-time-code"
                                   maxlength="{{ \App\Services\OtpService::CODE_LENGTH }}"
                                   placeholder="{{ str_repeat('0', \App\Services\OtpService::CODE_LENGTH) }}"
                                   class="w-full border-0 bg-transparent px-4 py-3 text-lg tracking-[0.5em] text-slate-900 outline-none placeholder:text-slate-300 focus:ring-0">
                        </x-auth.field>
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-emerald-700 px-5 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                        Verify and continue
                    </button>
                </form>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm">
                    <form method="POST" action="{{ route('otp.resend') }}">
                        @csrf
                        <button type="submit" class="font-semibold text-emerald-700 hover:text-emerald-900">Resend code</button>
                    </form>

                    <form method="POST" action="{{ route('otp.cancel') }}">
                        @csrf
                        <button type="submit" class="text-slate-500 hover:text-slate-700">Use a different account</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
