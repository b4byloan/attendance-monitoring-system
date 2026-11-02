<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e3a8a">
    <title>{{ $schoolSettings->school_name }} | School Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scroll-behavior: smooth; }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fade-in .65s ease-out both; }
        .fade-in-delay { animation-delay: .15s; }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .fade-in { animation: none; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div id="page-loader" class="fixed inset-0 z-50 hidden items-center justify-center bg-white/85 backdrop-blur-sm" aria-hidden="true">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-100 border-t-blue-800"></div>
    </div>

    <div class="border-b border-blue-950 bg-blue-950 text-sm text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-1 px-5 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <span>Official School Attendance Portal</span>
            <span>{{ $schoolSettings->academic_year ? 'School Year ' . $schoolSettings->academic_year : 'Public School Portal' }}</span>
        </div>
    </div>

    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Main navigation">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                @if ($schoolSettings->school_logo_path)
                    <img src="{{ asset('storage/' . $schoolSettings->school_logo_path) }}"
                         alt="{{ $schoolSettings->school_name }} logo"
                         class="h-14 w-14 rounded-full object-contain">
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-800 text-2xl text-white">🏫</span>
                @endif
                <span>
                    <span class="block text-lg font-bold leading-tight text-blue-950 sm:text-xl">{{ $schoolSettings->school_name }}</span>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Attendance Monitoring System</span>
                </span>
            </a>
            <a href="{{ route('login') }}" data-navigation
               class="rounded-md bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                Portal Login
            </a>
        </nav>
    </header>

    <main>
        <section class="relative overflow-hidden bg-blue-900">
            <div class="absolute inset-y-0 right-0 hidden w-1/3 bg-blue-950/40 lg:block"></div>
            <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[1.25fr_.75fr] lg:py-24">
                <div class="fade-in">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-yellow-300">Public School Service Portal</p>
                    <h1 class="mt-4 max-w-3xl text-4xl font-bold leading-tight text-white sm:text-5xl">
                        Reliable attendance records for every learner
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-blue-100">
                        Supporting daily school operations through accurate QR attendance, organized student records, timely notifications, and accessible reports.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('login') }}" data-navigation
                           class="rounded-md bg-yellow-400 px-6 py-3 text-center font-bold text-blue-950 transition hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-blue-900">
                            Open School Portal
                        </a>
                        <a href="#services"
                           class="rounded-md border border-blue-300 px-6 py-3 text-center font-semibold text-white transition hover:bg-white/10">
                            View Services
                        </a>
                    </div>
                </div>

                <aside class="fade-in fade-in-delay border-t-4 border-yellow-400 bg-white shadow-xl" aria-label="School information">
                    <div class="p-6 sm:p-8">
                        <h2 class="text-xl font-bold text-blue-950">School Information</h2>
                        <dl class="mt-5 space-y-5 text-sm">
                            <div>
                                <dt class="font-semibold uppercase tracking-wide text-slate-500">School Name</dt>
                                <dd class="mt-1 font-medium text-slate-800">{{ $schoolSettings->school_name }}</dd>
                            </div>
                            @if ($schoolSettings->school_address)
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide text-slate-500">Address</dt>
                                    <dd class="mt-1 leading-6 text-slate-800">{{ $schoolSettings->school_address }}</dd>
                                </div>
                            @endif
                            @if ($schoolSettings->school_contact_number)
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide text-slate-500">Contact Number</dt>
                                    <dd class="mt-1 text-slate-800">{{ $schoolSettings->school_contact_number }}</dd>
                                </div>
                            @endif
                            @if ($schoolSettings->school_email)
                                <div>
                                    <dt class="font-semibold uppercase tracking-wide text-slate-500">Email</dt>
                                    <dd class="mt-1 break-all text-blue-800">{{ $schoolSettings->school_email }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </aside>
            </div>
        </section>

        <section id="services" class="px-5 py-16 sm:px-8 sm:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-widest text-blue-800">School Services</p>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900">Attendance support for the school community</h2>
                    <p class="mt-4 leading-7 text-slate-600">
                        The portal provides role-based access for authorized administrators, school personnel, guardians, and students.
                    </p>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        ['01', 'QR Attendance', 'Accurate and efficient check-in and check-out recording using student QR codes.'],
                        ['02', 'Student Records', 'Organized learner information, sections, attendance history, and status.'],
                        ['03', 'Reports', 'Clear daily summaries and exportable attendance reports for school operations.'],
                        ['04', 'Notifications', 'Timely communication between the school, authorized staff, and guardians.']
                    ] as $service)
                        <article class="border border-slate-200 border-t-4 border-t-blue-800 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                            <span class="text-sm font-bold text-yellow-600">{{ $service[0] }}</span>
                            <h3 class="mt-3 text-lg font-bold text-blue-950">{{ $service[1] }}</h3>
                            <p class="mt-3 leading-7 text-slate-600">{{ $service[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="border-y border-slate-200 bg-white px-5 py-14 sm:px-8">
            <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1fr_auto] md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-blue-950">Authorized School Personnel</h2>
                    <p class="mt-3 max-w-2xl leading-7 text-slate-600">
                        Sign in using the account assigned by the school administrator. Access is limited according to your assigned role.
                    </p>
                </div>
                <a href="{{ route('login') }}" data-navigation
                   class="rounded-md bg-blue-800 px-6 py-3 text-center font-semibold text-white transition hover:bg-blue-900">
                    Sign in to the Portal
                </a>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 px-5 py-8 text-slate-300 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $schoolSettings->school_name }} &mdash; Attendance Monitoring System</p>
            <p>&copy; {{ date('Y') }} {{ $schoolSettings->school_name }}</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('[data-navigation]').forEach(function (link) {
            link.addEventListener('click', function () {
                const loader = document.getElementById('page-loader');
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            });
        });
    </script>
</body>
</html>
