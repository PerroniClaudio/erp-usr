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

    <div class="flex flex-col gap-4 mt-4">
        @foreach ($users as $user)
            @php
                $rows = $defaultSchedulesByUser[$user->id] ?? collect();
            @endphp

            <div class="card bg-base-300 shadow-lg">
                <div class="card-body flex flex-col gap-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="space-y-1">
                            <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
                            <p class="text-sm text-base-content/70">
                                {{ __('personnel.users_weekly_schedule_intro', ['name' => $user->name]) }}
                            </p>
                        </div>
                        <div class="badge badge-outline">
                            {{ __('personnel.users_weekly_hours') }}: {{ $user->weekly_hours ?? '-' }}
                        </div>
                    </div>

                    <div class="user-weekly-scheduler space-y-3" data-user-id="{{ $user->id }}"
                        data-user-name="{{ $user->name }}" data-week-start="{{ $weekStart->toDateString() }}"
                        data-schedules='@json($rows)'
                        data-save-url="{{ route('user-schedules.store') }}"
                        data-weekday-labels='@json($dayLabelsLong)'
                        data-weekday-short-labels='@json($dayLabelsShort)'
                        data-attendance-types='@json($attendanceTypesPayload)'
                        data-default-attendance-type="{{ $defaultAttendanceTypeId }}"
                        data-label-add="{{ __('personnel.users_weekly_schedule_modal_add') }}"
                        data-label-edit="{{ __('personnel.users_weekly_schedule_modal_edit') }}"
                        data-error-end="{{ __('personnel.users_default_schedule_error_end_before_start') }}"
                        data-error-save="{{ __('personnel.users_default_schedule_save_error') }}"
                        data-empty-text="{{ __('personnel.users_default_schedule_empty') }}">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-sm text-base-content/70">
                                {{ __('personnel.users_weekly_schedule_hint') }}
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn btn-primary btn-sm add-slot">
                                    <x-lucide-plus class="w-4 h-4" />
                                    {{ __('personnel.users_default_schedule_add_slot') }}
                                </button>
                                <button type="button" class="btn btn-primary btn-sm save-weekly-schedule">
                                    <x-lucide-save class="w-4 h-4" />
                                    {{ __('personnel.users_default_schedule_save') }}
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                            <div class="xl:col-span-3 bg-base-200 rounded-xl p-3 border border-base-300">
                                <div class="text-sm font-semibold text-base-content/70 flex items-center gap-2 mb-2">
                                    <x-lucide-calendar class="w-4 h-4" />
                                    {{ __('personnel.users_weekly_schedule_calendar_title') }}
                                </div>
                                <div
                                    class="user-weekly-calendar rounded-lg bg-base-100 p-2 border border-base-200 min-h-[320px]">
                                </div>
                            </div>

                            <div class="xl:col-span-2 bg-base-200 rounded-xl p-3 border border-base-300">
                                <div class="text-sm font-semibold text-base-content/70 flex items-center gap-2 mb-2">
                                    <x-lucide-list-check class="w-4 h-4" />
                                    {{ __('personnel.users_weekly_schedule_summary_title') }}
                                </div>
                                <div class="weekly-summary text-sm space-y-2" aria-live="polite">
                                    <p class="text-xs text-base-content/60">
                                        {{ __('personnel.users_default_schedule_empty') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <dialog id="weekly-schedule-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-semibold text-lg mb-2" data-modal-title></h3>
            <div class="flex flex-col gap-3">
                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_day') }}</span>
                    <select id="weekly-modal-day-select" class="select select-bordered w-full">
                        <option value="monday">{{ __('personnel.users_default_schedule_monday') }}</option>
                        <option value="tuesday">{{ __('personnel.users_default_schedule_tuesday') }}</option>
                        <option value="wednesday">{{ __('personnel.users_default_schedule_wednesday') }}</option>
                        <option value="thursday">{{ __('personnel.users_default_schedule_thursday') }}</option>
                        <option value="friday">{{ __('personnel.users_default_schedule_friday') }}</option>
                        <option value="saturday">{{ __('personnel.users_default_schedule_saturday') }}</option>
                        <option value="sunday">{{ __('personnel.users_default_schedule_sunday') }}</option>
                    </select>
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_start') }}</span>
                    <input type="time" id="weekly-modal-hour-start" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_end') }}</span>
                    <input type="time" id="weekly-modal-hour-end" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_type') }}</span>
                    <select id="weekly-modal-type" class="select select-bordered w-full">
                        @foreach ($attendanceTypes as $attendanceType)
                            <option value="{{ $attendanceType->id }}">
                                {{ $attendanceType->name }} ({{ $attendanceType->acronym }})
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost"
                    id="weekly-modal-cancel">{{ __('personnel.users_default_schedule_cancel') }}</button>
                <button class="btn btn-error"
                    id="weekly-modal-delete">{{ __('personnel.users_default_schedule_delete') }}</button>
                <button class="btn btn-primary"
                    id="weekly-modal-save">{{ __('personnel.users_default_schedule_save') }}</button>
            </div>
        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/user-schedules.js')
    @endpush
</x-layouts.app>
