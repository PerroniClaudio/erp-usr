<x-layouts.app>
    <div class="flex flex-col gap-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold">{{ __('personnel.user_schedule_request_title') }}</h1>
                <p class="text-base-content/70">
                    {{ __('personnel.user_schedule_request_description') }}
                </p>
            </div>

            <form method="GET" action="{{ route('user-schedule-request.index') }}" class="flex items-center gap-2">
                <input type="date" name="week_start" value="{{ $weekStart->toDateString() }}"
                    min="{{ $minimumWeekStart->toDateString() }}" class="input input-bordered" />
                <button type="submit" class="btn btn-secondary">
                    {{ __('personnel.users_default_schedule_go') }}
                </button>
            </form>
        </div>

        <p class="text-base-content/70">
            {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
        </p>
    </div>

    @if ($pendingRequest)
        <div class="alert alert-info mt-4">
            <x-lucide-info class="w-5 h-5" />
            <div>
                <h3 class="font-semibold">{{ __('personnel.user_schedule_request_pending_title') }}</h3>
                <p class="text-sm">{{ __('personnel.user_schedule_request_pending_desc') }}</p>
            </div>
        </div>
    @endif

    <div class="mt-6">
        @include('admin.personnel.users.partials.weekly-schedule-card', [
            'user' => $user,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'scheduleRows' => $scheduleRows,
            'hasExisting' => false,
            'timeOffEntries' => collect(),
            'dayLabelsLong' => $dayLabelsLong,
            'dayLabelsShort' => $dayLabelsShort,
            'attendanceTypes' => $attendanceTypes,
            'attendanceTypesPayload' => $attendanceTypesPayload,
            'defaultAttendanceTypeId' => $defaultAttendanceTypeId,
            'saveUrl' => route('user-schedule-request.store'),
            'saveButtonLabel' => __('personnel.user_schedule_request_submit'),
            'holidayDays' => $holidayDays,
        ])
    </div>

    @include('admin.personnel.users.partials.weekly-schedule-modal', ['attendanceTypes' => $attendanceTypes])

    @push('scripts')
        @vite('resources/js/user-schedules.js')
    @endpush
</x-layouts.app>
