<x-layouts.app :shouldHavePadding=false>
    @php
        $orderedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayLabelsLong = [
            'monday' => __('personnel.users_default_schedule_monday'),
            'tuesday' => __('personnel.users_default_schedule_tuesday'),
            'wednesday' => __('personnel.users_default_schedule_wednesday'),
            'thursday' => __('personnel.users_default_schedule_thursday'),
            'friday' => __('personnel.users_default_schedule_friday'),
            'saturday' => __('personnel.users_default_schedule_saturday'),
            'sunday' => __('personnel.users_default_schedule_sunday'),
        ];
        $dayLabelsShort = [
            'monday' => __('personnel.users_default_schedule_monday_short'),
            'tuesday' => __('personnel.users_default_schedule_tuesday_short'),
            'wednesday' => __('personnel.users_default_schedule_wednesday_short'),
            'thursday' => __('personnel.users_default_schedule_thursday_short'),
            'friday' => __('personnel.users_default_schedule_friday_short'),
            'saturday' => __('personnel.users_default_schedule_saturday_short'),
            'sunday' => __('personnel.users_default_schedule_sunday_short'),
        ];
    @endphp

    @php
        $activeUser = $users->first();
        $activeRows = $activeUser ? $defaultSchedulesByUser[$activeUser->id] ?? collect() : collect();
        $activeExisting = $activeUser ? $existingSchedulesByUser[$activeUser->id] ?? collect() : collect();
        $activeHasExisting = $activeExisting->isNotEmpty();
        $activeScheduleRows = $activeHasExisting ? $activeExisting : $activeRows;
        $activeTimeOffEntries = collect($activeUser ? $timeOffByUser[$activeUser->id] ?? [] : []);
    @endphp

    <div class="drawer lg:drawer-open">
        <input id="weekly-schedules-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col px-4 pb-16">
            <div class="container mx-auto flex mb-4">
                <label for="weekly-schedules-drawer" class="btn btn-secondary drawer-button w-full lg:hidden">
                    {{ __('personnel.users_weekly_schedule_title') }}
                </label>
            </div>


            <main class="container mx-auto flex flex-col gap-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="space-y-1">
                        <h1 class="text-3xl sm:text-4xl">{{ __('personnel.users_weekly_schedule_title') }}</h1>
                    </div>
                    <div class="badge badge-outline">
                        {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
                    </div>
                </div>

                <hr>

                <div class="flex-1 space-y-4" id="user-schedule-detail"
                    data-week-start="{{ $weekStart->toDateString() }}" data-active-user="{{ $activeUser->id ?? '' }}">
                    @if ($activeUser)
                        @include('admin.personnel.users.partials.weekly-schedule-card', [
                            'user' => $activeUser,
                            'weekStart' => $weekStart,
                            'weekEnd' => $weekEnd,
                            'scheduleRows' => $activeScheduleRows,
                            'hasExisting' => $activeHasExisting,
                            'timeOffEntries' => $activeTimeOffEntries,
                            'dayLabelsLong' => $dayLabelsLong,
                            'dayLabelsShort' => $dayLabelsShort,
                            'attendanceTypes' => $attendanceTypes,
                            'attendanceTypesPayload' => $attendanceTypesPayload,
                            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
                            'holidayDays' => $holidayDays,
                        ])
                    @else
                        <div class="card bg-base-200">
                            <div class="card-body">
                                <p class="text-sm text-base-content/70">
                                    {{ __('personnel.users_default_schedule_empty') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </main>
        </div>
        <div class="drawer-side z-50">
            <label for="weekly-schedules-drawer" class="drawer-overlay" aria-label="Chiudi il menu"></label>
            <div class="bg-base-200 text-base-content min-h-full w-80 p-4 space-y-6 overflow-y-auto">
                <div class="space-y-3">
                    <div class="space-y-1">
                        <p class="text-xs uppercase font-semibold text-base-content/60">
                            {{ __('personnel.users_weekly_schedule_week_label') }}
                        </p>
                        <p class="text-xl font-semibold text-primary">
                            {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
                        </p>
                        <p class="text-sm text-base-content/70">
                            {{ __('personnel.users_weekly_schedule_week_help') }}
                        </p>
                    </div>
                    <form method="GET" action="{{ route('user-schedules.index') }}"
                        class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-2 items-end">
                        <label class="form-control w-full">
                            <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}"
                                class="input input-bordered w-full" />
                        </label>
                        <div class="flex sm:items-end">
                            <button class="btn btn-secondary w-full sm:w-auto"
                                type="submit">{{ __('personnel.users_default_schedule_go') }}</button>
                        </div>
                        <p class="text-xs text-base-content/60 sm:col-span-2">
                            {{ __('personnel.users_weekly_schedule_week_selector_hint') }}
                        </p>
                    </form>
                </div>

                <div class="space-y-2">
                    <h2 class="text-sm font-semibold text-base-content/70 uppercase">{{ __('personnel.users') }}</h2>
                    <div class="flex flex-col gap-1">
                        @foreach ($users as $user)
                            @php
                                $hasExisting =
                                    isset($existingSchedulesByUser[$user->id]) &&
                                    $existingSchedulesByUser[$user->id]->isNotEmpty();
                                $hasTimeOff = !empty($timeOffByUser[$user->id]);
                            @endphp
                            <button type="button" class="btn btn-ghost btn-sm justify-between w-full text-left"
                                data-user-nav="{{ $user->id }}" data-has-existing="{{ $hasExisting ? '1' : '0' }}"
                                data-fetch-url="{{ route('user-schedules.show', $user) }}">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="status-dot inline-flex h-2.5 w-2.5 rounded-full ring-4 {{ $hasExisting ? 'bg-success/80 ring-success/10' : 'bg-error/80 ring-error/10' }}"
                                        data-status-dot aria-hidden="true"></span>
                                    <span>{{ $user->name }}</span>
                                </span>
                                <span class="flex items-center gap-1 text-base-content/80">
                                    @if ($hasTimeOff)
                                        <x-lucide-sun class="w-4 h-4 text-warning" />
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.personnel.users.partials.weekly-schedule-modal', [
        'attendanceTypes' => $attendanceTypes,
    ])

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const navButtons = document.querySelectorAll('[data-user-nav]');
                const detail = document.getElementById('user-schedule-detail');

                if (!detail || !navButtons.length) return;

                const setActiveButton = (userId) => {
                    navButtons.forEach((btn) => {
                        const isActive = btn.dataset.userNav === userId;
                        btn.classList.toggle('btn-primary', isActive);
                        btn.classList.toggle('btn-ghost', !isActive);
                    });
                };

                const loadUser = (button) => {
                    const userId = button.dataset.userNav;
                    if (detail.dataset.activeUser === userId) {
                        setActiveButton(userId);
                        return;
                    }

                    const url = new URL(button.dataset.fetchUrl, window.location.origin);
                    if (detail.dataset.weekStart) {
                        url.searchParams.set('week_start', detail.dataset.weekStart);
                    }

                    detail.classList.add('opacity-50');

                    axios
                        .get(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        })
                        .then(({
                            data
                        }) => {
                            detail.innerHTML = data.html;
                            detail.dataset.activeUser = userId;
                            window.initWeeklySchedulers?.(detail);
                            setActiveButton(userId);
                        })
                        .catch(() => {
                            alert('Errore nel caricamento dei dati utente.');
                        })
                        .finally(() => {
                            detail.classList.remove('opacity-50');
                        });
                };

                navButtons.forEach((button) => {
                    button.addEventListener('click', () => loadUser(button));
                });

                setActiveButton(detail.dataset.activeUser);
            });
        </script>
        @vite('resources/js/user-schedules.js')
    @endpush
</x-layouts.app>
