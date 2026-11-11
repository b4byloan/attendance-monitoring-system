<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - {{ $schoolSettings->school_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-emerald-950 text-slate-800 antialiased lg:bg-slate-50">
    <div class="relative grid min-h-screen lg:grid-cols-[1fr_520px]">
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
                        <span class="text-xs font-semibold uppercase tracking-widest text-emerald-100">Student &amp; Parent Portal</span>
                    </span>
                </a>
            </div>

            <div class="relative hidden px-12 pb-14 lg:block">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-yellow-300">Stay informed</p>
                <h1 class="mt-4 max-w-xl text-4xl font-bold leading-tight text-white">Create your school portal account.</h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-emerald-50">Register as a parent or student to view attendance, announcements, and the information you need in one secure portal.</p>
            </div>
        </section>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-8">
            <div class="w-full max-w-md rounded-2xl bg-white/95 p-6 shadow-2xl ring-1 ring-white/60 backdrop-blur sm:p-8 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0 lg:backdrop-blur-none">
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

                <p class="text-sm font-bold uppercase tracking-[0.16em] text-emerald-700">Registration</p>
                <h2 class="mt-2 text-3xl font-bold text-emerald-950">Create an account</h2>
                <p class="mt-2 text-sm text-slate-500">Choose the type of account you want to create.</p>

                <div class="mt-6" data-register-tabs data-active="{{ $activeTab }}">
                    <div role="tablist" aria-label="Registration type" class="grid grid-cols-2 gap-1 rounded-xl bg-emerald-50 p-1 ring-1 ring-emerald-100">
                        <button type="button" role="tab" id="parent-tab" aria-controls="parent-panel"
                                aria-selected="{{ $activeTab === 'parent' ? 'true' : 'false' }}"
                                data-register-tab="parent"
                                @class([
                                    'rounded-lg px-2 py-2.5 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 sm:px-3 sm:text-sm',
                                    'bg-white text-emerald-800 shadow-sm ring-1 ring-emerald-200' => $activeTab === 'parent',
                                    'text-emerald-700 hover:bg-white/60 hover:text-emerald-900' => $activeTab !== 'parent',
                                ])>
                            Parent Registration
                        </button>
                        <button type="button" role="tab" id="student-tab" aria-controls="student-panel"
                                aria-selected="{{ $activeTab === 'student' ? 'true' : 'false' }}"
                                data-register-tab="student"
                                @class([
                                    'rounded-lg px-2 py-2.5 text-xs font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2 sm:px-3 sm:text-sm',
                                    'bg-white text-emerald-800 shadow-sm ring-1 ring-emerald-200' => $activeTab === 'student',
                                    'text-emerald-700 hover:bg-white/60 hover:text-emerald-900' => $activeTab !== 'student',
                                ])>
                            Student Registration
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-5 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <x-ui.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <div id="parent-panel" role="tabpanel" aria-labelledby="parent-tab" tabindex="0"
                     data-register-panel="parent"
                     @class(['mt-6 transition-opacity duration-200', 'hidden' => $activeTab !== 'parent'])
                     @if ($activeTab !== 'parent') hidden @endif>
                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="registration_type" value="parent">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700">Full name</label>
                            <input id="name" type="text" name="name" value="{{ old('registration_type') === 'parent' ? old('name') : '' }}" required @if ($activeTab === 'parent') autofocus @endif autocomplete="name"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('registration_type') === 'parent' ? old('email') : '' }}" required autocomplete="email"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label for="phone_number" class="block text-sm font-semibold text-slate-700">Phone number</label>
                            <input id="phone_number" type="tel" name="phone_number" value="{{ old('registration_type') === 'parent' ? old('phone_number') : '' }}" required autocomplete="tel"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            <p class="mt-1 text-xs text-slate-500">Use at least 8 characters.</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-emerald-700 px-5 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                            Create parent account
                        </button>
                    </form>
                </div>

                <div id="student-panel" role="tabpanel" aria-labelledby="student-tab" tabindex="0"
                     data-register-panel="student"
                     @class(['mt-6 transition-opacity duration-200', 'hidden' => $activeTab !== 'student'])
                     @if ($activeTab !== 'student') hidden @endif>
                    <form method="POST" action="{{ route('register.student') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="registration_type" value="student">
                        <div>
                            <label for="lrn" class="block text-sm font-semibold text-slate-700">LRN (12 digits)</label>
                            <input id="lrn" type="text" name="lrn" value="{{ old('registration_type') === 'student' ? old('lrn') : '' }}" required @if ($activeTab === 'student') autofocus @endif
                                   inputmode="numeric" maxlength="12" pattern="\d{12}" autocomplete="off"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-slate-700">First name</label>
                                <input id="first_name" type="text" name="first_name" value="{{ old('registration_type') === 'student' ? old('first_name') : '' }}" required autocomplete="given-name"
                                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-slate-700">Last name</label>
                                <input id="last_name" type="text" name="last_name" value="{{ old('registration_type') === 'student' ? old('last_name') : '' }}" required autocomplete="family-name"
                                       class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            </div>
                        </div>
                        <div>
                            <label for="middle_name" class="block text-sm font-semibold text-slate-700">Middle name <span class="font-normal text-slate-500">(optional)</span></label>
                            <input id="middle_name" type="text" name="middle_name" value="{{ old('registration_type') === 'student' ? old('middle_name') : '' }}" autocomplete="additional-name"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="grade_level" class="block text-sm font-semibold text-slate-700">Grade level</label>
                                <select id="grade_level" name="grade_level" required data-grade-level-select
                                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                                    <option value="">Select grade level</option>
                                    @foreach ($gradeLevels as $gradeLevel)
                                        <option value="{{ $gradeLevel }}" @selected(old('registration_type') === 'student' && old('grade_level') === $gradeLevel)>{{ $gradeLevel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="section_id" class="block text-sm font-semibold text-slate-700">Section</label>
                                <select id="section_id" name="section_id" required data-section-select
                                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                                    <option value="">Select a section</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}" @selected(old('registration_type') === 'student' && old('section_id') == $section->id)>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="student_email" class="block text-sm font-semibold text-slate-700">Email address</label>
                            <input id="student_email" type="email" name="email" value="{{ old('registration_type') === 'student' ? old('email') : '' }}" required autocomplete="email"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label for="student_password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <input id="student_password" type="password" name="password" required autocomplete="new-password"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            <p class="mt-1 text-xs text-slate-500">Use at least 8 characters.</p>
                        </div>
                        <div>
                            <label for="student_password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
                            <input id="student_password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-emerald-700 px-5 py-3 font-bold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                            Create student account
                        </button>
                    </form>
                </div>

                <p class="mt-7 text-center text-sm text-slate-600">
                    Already registered?
                    <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:text-emerald-900">Sign in</a>
                </p>
            </div>
        </main>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabsContainer = document.querySelector('[data-register-tabs]');

                if (!tabsContainer) {
                    return;
                }

                const tabs = Array.from(tabsContainer.querySelectorAll('[data-register-tab]'));
                const panels = Array.from(document.querySelectorAll('[data-register-panel]'));

                function activateTab(name, moveFocus) {
                    tabs.forEach(function (tab) {
                        const isActive = tab.dataset.registerTab === name;

                        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        tab.classList.toggle('bg-white', isActive);
                        tab.classList.toggle('text-emerald-800', isActive);
                        tab.classList.toggle('shadow-sm', isActive);
                        tab.classList.toggle('ring-1', isActive);
                        tab.classList.toggle('ring-emerald-200', isActive);
                        tab.classList.toggle('text-emerald-700', !isActive);
                        tab.classList.toggle('hover:bg-white/60', !isActive);
                        tab.classList.toggle('hover:text-emerald-900', !isActive);

                        if (isActive && moveFocus) {
                            tab.focus();
                        }
                    });

                    panels.forEach(function (panel) {
                        const isActive = panel.dataset.registerPanel === name;

                        panel.hidden = !isActive;
                        panel.classList.toggle('hidden', !isActive);

                        if (isActive) {
                            panel.classList.add('opacity-0');
                            void panel.offsetWidth;
                            panel.classList.remove('opacity-0');
                        }
                    });
                }

                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        activateTab(tab.dataset.registerTab, false);
                    });
                });

                tabsContainer.addEventListener('keydown', function (event) {
                    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                        return;
                    }

                    const currentIndex = tabs.indexOf(document.activeElement);

                    if (currentIndex === -1) {
                        return;
                    }

                    event.preventDefault();

                    const nextIndex = event.key === 'ArrowRight'
                        ? (currentIndex + 1) % tabs.length
                        : (currentIndex - 1 + tabs.length) % tabs.length;

                    activateTab(tabs[nextIndex].dataset.registerTab, true);
                });

                const gradeLevelSelect = document.querySelector('[data-grade-level-select]');
                const sectionSelect = document.querySelector('[data-section-select]');

                if (gradeLevelSelect && sectionSelect) {
                    const sectionOptions = Array.from(sectionSelect.options).filter(function (option) {
                        return option.value !== '';
                    });

                    function syncSections() {
                        const gradeLevel = gradeLevelSelect.value;
                        const selectedSection = sectionSelect.value;
                        let keepSelection = selectedSection === '';

                        sectionOptions.forEach(function (option) {
                            const matchesGrade = gradeLevel === '' || option.dataset.gradeLevel === gradeLevel;

                            option.hidden = !matchesGrade;
                            option.disabled = !matchesGrade;

                            if (matchesGrade && option.value === selectedSection) {
                                keepSelection = true;
                            }
                        });

                        sectionSelect.disabled = gradeLevel === '';

                        if (!keepSelection) {
                            sectionSelect.value = '';
                        }
                    }

                    gradeLevelSelect.addEventListener('change', syncSections);
                    syncSections();
                }
            });
        </script>
    @endpush

    @stack('scripts')
</body>
</html>
