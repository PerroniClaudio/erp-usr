@php
    $scheduleRows = collect($scheduleRows ?? []);
    $existingRows = collect($existingRows ?? []);
    $saveUrl = route('weekly-scheduled-timeoff.store');
    $allowEditing = true;
    $calendarWeekStart = $weekStart->copy();
    $calendarWeekEnd = ($weekEnd ?? $calendarWeekStart->copy()->addDays(6))->copy();
    $calendarWeekStartLabel = $calendarWeekStart->copy()->locale(app()->getLocale())->translatedFormat('d/m/Y');
    $calendarWeekEndLabel = $calendarWeekEnd->copy()->locale(app()->getLocale())->translatedFormat('d/m/Y');
@endphp

<div class="card bg-base-300 shadow-lg" data-user-card="{{ $user->id }}">
    <div class="card-body flex flex-col gap-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="space-y-1">
                <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
                <p class="text-sm text-base-content/70">
                    {{ __('personnel.users_weekly_timeoff_intro', ['name' => $user->name]) }}
                </p>
            </div>
            <div class="badge badge-outline">
                {{ $weekStart->format('d/m') }} → {{ $weekEnd->format('d/m') }}
            </div>
        </div>

        <div class="user-weekly-timeoff space-y-3" data-user-id="{{ $user->id }}"
            data-user-name="{{ $user->name }}" data-week-start="{{ $weekStart->toDateString() }}"
            data-schedules='@json($scheduleRows->values())'
            data-existing='@json($existingRows->values())'
            data-save-url="{{ $saveUrl }}"
            data-weekday-labels='@json($dayLabelsLong)'
            data-weekday-short-labels='@json($dayLabelsShort)'
            data-timeoff-types='@json($timeOffTypesPayload)'
            data-default-timeoff-type="{{ $defaultTimeOffTypeId }}"
            data-label-add="{{ __('personnel.users_scheduled_time_off_modal_add') }}"
            data-label-edit="{{ __('personnel.users_scheduled_time_off_modal_edit') }}"
            data-error-end="{{ __('personnel.users_scheduled_time_off_error_end_before_start') }}"
            data-error-save="{{ __('personnel.users_scheduled_time_off_save_error') }}"
            data-empty-text="{{ __('personnel.users_weekly_timeoff_empty') }}">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <p class="text-sm text-base-content/70">
                    {{ __('personnel.users_weekly_timeoff_hint') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm add-slot"
                        @if (! $allowEditing) disabled @endif>
                        <x-lucide-plus class="w-4 h-4" />
                        {{ __('personnel.users_scheduled_time_off_add_slot') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-sm save-weekly-timeoff"
                        @if (! $allowEditing) disabled @endif>
                        <x-lucide-save class="w-4 h-4" />
                        {{ __('personnel.users_scheduled_time_off_save') }}
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
                </div>
            </div>
        </div>
    </div>
</div>
