@php
    $scheduleRows = collect($scheduleRows ?? []);
    $timeOffEntries = collect($timeOffEntries ?? []);
    $holidayDays = collect($holidayDays ?? []);
    $saveUrl = $saveUrl ?? route('user-schedules.store');
    $saveButtonLabel = $saveButtonLabel ?? __('personnel.users_default_schedule_save');
    $successMessage = $successMessage ?? __('personnel.users_default_schedule_save_success');
    $successRedirect = $successRedirect ?? '';
    $allowEditing = $allowEditing ?? true;
    $calendarWeekStart = $weekStart->copy();
    $calendarWeekEnd = ($weekEnd ?? $calendarWeekStart->copy()->addDays(6))->copy();
    $calendarWeekStartLabel = $calendarWeekStart->copy()->locale(app()->getLocale())->translatedFormat('d/m/Y');
    $calendarWeekEndLabel = $calendarWeekEnd->copy()->locale(app()->getLocale())->translatedFormat('d/m/Y');

    $holidayDates = $holidayDays->pluck('date')->filter()->unique()->values();
    if ($holidayDates->isNotEmpty()) {
        $scheduleRows = $scheduleRows->filter(function ($row) use ($holidayDates) {
            $date = $row['date'] ?? null;
            return empty($date) || ! $holidayDates->contains($date);
        })->values();
    }

    $holidayPayload = $holidayDays
        ->map(function ($holiday) {
            $date = isset($holiday['date']) ? \Carbon\Carbon::parse($holiday['date'])->toDateString() : null;
            if (! $date) {
                return null;
            }

            return [
                'date' => $date,
                'label' => $holiday['label'] ?? __('personnel.users_weekly_schedule_holiday_badge'),
            ];
        })
        ->filter()
        ->values();
@endphp

<div class="card bg-base-300 shadow-lg" data-user-card="{{ $user->id }}">
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

        @if (!empty($hasExisting))
            <div class="alert alert-warning text-sm">
                {{ __('personnel.users_weekly_schedule_existing_note') }}
            </div>
        @endif

        <div class="user-weekly-scheduler space-y-3" data-user-id="{{ $user->id }}"
            data-user-name="{{ $user->name }}" data-week-start="{{ $weekStart->toDateString() }}"
            data-schedules='@json($scheduleRows->values())'
            data-time-off='@json($timeOffEntries->values())'
            data-save-url="{{ $saveUrl }}"
            data-weekday-labels='@json($dayLabelsLong)'
            data-weekday-short-labels='@json($dayLabelsShort)'
            data-attendance-types='@json($attendanceTypesPayload)'
            data-default-attendance-type="{{ $defaultAttendanceTypeId }}"
            data-label-add="{{ __('personnel.users_weekly_schedule_modal_add') }}"
            data-label-edit="{{ __('personnel.users_weekly_schedule_modal_edit') }}"
            data-error-end="{{ __('personnel.users_default_schedule_error_end_before_start') }}"
            data-error-save="{{ __('personnel.users_default_schedule_save_error') }}"
            data-holiday-error="{{ __('personnel.users_weekly_schedule_holiday_error') }}"
            data-holiday-label="{{ __('personnel.users_weekly_schedule_holiday_badge') }}"
            data-holidays='@json($holidayPayload)'
            data-empty-text="{{ __('personnel.users_default_schedule_empty') }}"
            data-success-message="{{ $successMessage }}"
            data-success-redirect="{{ $successRedirect }}"
            data-readonly="{{ $allowEditing ? 'false' : 'true' }}">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <p class="text-sm text-base-content/70">
                    {{ __('personnel.users_weekly_schedule_hint') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm add-slot"
                        @if (! $allowEditing) disabled @endif>
                        <x-lucide-plus class="w-4 h-4" />
                        {{ __('personnel.users_default_schedule_add_slot') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-sm save-weekly-schedule"
                        @if (! $allowEditing) disabled @endif>
                        <x-lucide-save class="w-4 h-4" />
                        {{ $saveButtonLabel }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-3 bg-base-200 rounded-xl p-3 border border-base-300">
                    <div class="text-sm font-semibold text-base-content/70 flex items-center gap-2 mb-2">
                        <x-lucide-calendar class="w-4 h-4" />
                        {{ 'Settimana dal ' . $calendarWeekStartLabel . ' al ' . $calendarWeekEndLabel }}
                    </div>
                    <div class="user-weekly-calendar rounded-lg bg-base-100 p-2 border border-base-200 min-h-[320px]">
                    </div>
                </div>

                <div class="flex flex-col gap-3 xl:col-span-2">
                    <div class="bg-base-200 rounded-xl p-3 border border-base-300">
                        <div class="text-sm font-semibold text-base-content/70 flex items-center gap-2 mb-2">
                            <x-lucide-list-check class="w-4 h-4" />
                            {{ __('personnel.users_weekly_schedule_summary_title') }}
                        </div>
                        <div class="weekly-summary text-sm space-y-2" aria-live="polite">
                            <p class="text-xs text-base-content/60">
                                {{ __('personnel.users_default_schedule_empty') }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-xl p-3 border border-base-300">
                        <div class="text-sm font-semibold text-base-content/70 flex items-center gap-2 mb-2">
                            <x-lucide-sun class="w-4 h-4" />
                            {{ __('personnel.users_weekly_schedule_timeoff_title') }}
                        </div>
                        <div class="text-sm space-y-2" aria-live="polite">
                            @forelse ($timeOffEntries as $entry)
                                @php
                                    $start = \Carbon\Carbon::parse($entry['start'])->locale(app()->getLocale());
                                    $end = \Carbon\Carbon::parse($entry['end'])->locale(app()->getLocale());
                                @endphp
                                <div class="rounded-lg border border-base-200 px-2 py-1 flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-xs uppercase text-base-content/60">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full border border-base-300"
                                            style="background-color: {{ $entry['color'] ?? '#94a3b8' }};"></span>
                                        {{ $entry['title'] ?? '-' }}
                                    </div>
                                    <div class="text-sm font-medium">
                                        {{ $start->translatedFormat('D d/m H:i') }}
                                        &rarr;
                                        {{ $end->translatedFormat('H:i') }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-base-content/60">
                                    {{ __('personnel.users_weekly_schedule_timeoff_empty') }}
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
