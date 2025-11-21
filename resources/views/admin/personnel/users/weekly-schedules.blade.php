<x-layouts.app>
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-semibold">{{ __('personnel.users_weekly_schedule_title') }}</h1>
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('user-schedules.index') }}" class="flex items-center gap-2">
                <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}"
                    class="input input-bordered" />
                <button class="btn btn-secondary"
                    type="submit">{{ __('personnel.users_default_schedule_go') }}</button>
            </form>
        </div>
    </div>
    <hr>

    <p class="text-base-content/70 mt-2">{{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}</p>

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
        $activeRows = $activeUser ? ($defaultSchedulesByUser[$activeUser->id] ?? collect()) : collect();
        $activeExisting = $activeUser ? ($existingSchedulesByUser[$activeUser->id] ?? collect()) : collect();
        $activeHasExisting = $activeExisting->isNotEmpty();
        $activeScheduleRows = $activeHasExisting ? $activeExisting : $activeRows;
        $activeTimeOffEntries = collect($activeUser ? ($timeOffByUser[$activeUser->id] ?? []) : []);
    @endphp

    <div class="flex flex-col lg:flex-row gap-4 mt-4">
        <aside class="lg:w-72">
            <div class="card bg-base-200 shadow-lg">
                <div class="card-body p-3 gap-2">
                    <h2 class="text-sm font-semibold text-base-content/70 uppercase">{{ __('personnel.users') }}</h2>
                    <div class="flex flex-col gap-1">
                        @foreach ($users as $user)
                            @php
                                $hasExisting = isset($existingSchedulesByUser[$user->id]) && $existingSchedulesByUser[$user->id]->isNotEmpty();
                                $hasTimeOff = ! empty($timeOffByUser[$user->id]);
                            @endphp
                            <button type="button"
                                class="btn btn-ghost btn-sm justify-between w-full text-left"
                                data-user-nav="{{ $user->id }}"
                                data-fetch-url="{{ route('user-schedules.show', $user) }}">
                                <span>{{ $user->name }}</span>
                                <span class="flex items-center gap-1">
                                    @if ($hasTimeOff)
                                        <x-lucide-sun class="w-4 h-4 text-warning" />
                                    @endif
                                    @if ($hasExisting)
                                        <x-lucide-check class="w-4 h-4 text-success" />
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 space-y-4" id="user-schedule-detail" data-week-start="{{ $weekStart->toDateString() }}"
            data-active-user="{{ $activeUser->id ?? '' }}">
            @if ($activeUser)
                @include('admin.personnel.users.partials.weekly-schedule-card', [
                    'user' => $activeUser,
                    'weekStart' => $weekStart,
                    'scheduleRows' => $activeScheduleRows,
                    'hasExisting' => $activeHasExisting,
                    'timeOffEntries' => $activeTimeOffEntries,
                    'dayLabelsLong' => $dayLabelsLong,
                    'dayLabelsShort' => $dayLabelsShort,
                    'attendanceTypes' => $attendanceTypes,
                    'attendanceTypesPayload' => $attendanceTypesPayload,
                    'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
                ])
            @else
                <div class="card bg-base-200">
                    <div class="card-body">
                        <p class="text-sm text-base-content/70">{{ __('personnel.users_default_schedule_empty') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('admin.personnel.users.partials.weekly-schedule-modal', ['attendanceTypes' => $attendanceTypes])

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

                    fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((response) => response.json())
                        .then((data) => {
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
