<x-layouts.app>
    <div class="flex items-center gap-2 text-sm text-base-content/70 mb-2">
        <a href="{{ route('admin.user-schedule-requests.index') }}" class="btn btn-ghost btn-sm">
            &larr; {{ __('personnel.user_schedule_request_admin_back') }}
        </a>
        <span class="text-base-content/50">/</span>
        <span>{{ $changeRequest->user->name }}</span>
    </div>

    <x-layouts.header :title="__('personnel.user_schedule_request_admin_request_info')">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <span class="badge {{ $changeRequest->status === \App\Models\UserScheduleChangeRequest::STATUS_PENDING ? 'badge-warning' : ($changeRequest->status === \App\Models\UserScheduleChangeRequest::STATUS_APPROVED ? 'badge-success' : 'badge-error') }}">
                    {{ __('personnel.user_schedule_request_admin_status_' . ($changeRequest->status === \App\Models\UserScheduleChangeRequest::STATUS_APPROVED ? 'approved' : ($changeRequest->status === \App\Models\UserScheduleChangeRequest::STATUS_DENIED ? 'denied' : 'pending'))) }}
                </span>
                @if ($canApprove)
                    <form action="{{ route('admin.user-schedule-requests.deny', $changeRequest) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-error btn-outline btn-sm">
                            {{ __('personnel.user_schedule_request_admin_deny') }}
                        </button>
                    </form>
                @endif
            </div>
        </x-slot:actions>
        <p class="text-base-content/70">
            {{ __('personnel.user_schedule_request_admin_week_range', ['range' => $weekStart->format('d/m/Y') . ' - ' . $weekEnd->format('d/m/Y')]) }}
        </p>
    </x-layouts.header>

    <div class="mt-6">
        @include('admin.personnel.users.partials.weekly-schedule-card', [
            'user' => $changeRequest->user,
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
            'saveUrl' => route('admin.user-schedule-requests.approve', $changeRequest),
            'saveButtonLabel' => __('personnel.user_schedule_request_admin_approve'),
            'successMessage' => __('personnel.user_schedule_request_admin_success'),
            'successRedirect' => route('admin.user-schedule-requests.index'),
            'allowEditing' => $canApprove,
            'holidayDays' => $holidayDays,
        ])
    </div>

    @include('admin.personnel.users.partials.weekly-schedule-modal', ['attendanceTypes' => $attendanceTypes])

    @push('scripts')
        @vite('resources/js/user-schedules.js')
    @endpush
</x-layouts.app>
