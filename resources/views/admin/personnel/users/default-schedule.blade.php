<x-layouts.app>
    <x-layouts.header :title="__('personnel.users_default_schedule_title')">
        <x-slot:actions>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                {{ __('personnel.users_default_schedule_back') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div id="default-schedule-toast" class="toast toast-bottom toast-end hidden">
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span>{{ __('personnel.users_default_schedule_save_success') }}</span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-base-content/70">{{ __('personnel.users_default_schedule_intro', ['name' => $user->name]) }}</p>
        <div class="flex gap-2">
            @if ($schedules->isEmpty())
                <form method="POST" action="{{ route('users.default-schedules.generate', $user) }}">
                    @csrf
                    <button class="btn btn-secondary"
                        type="submit">{{ __('personnel.users_default_schedule_generate') }}</button>
                </form>
            @endif
            <button class="btn btn-secondary"
                id="add-work-slot">{{ __('personnel.users_default_schedule_add_slot') }}</button>
            <button class="btn btn-primary"
                id="save-schedule">{{ __('personnel.users_default_schedule_save') }}</button>
        </div>
    </div>


    <div class="card bg-base-300">
        <div class="card-body flex flex-col gap-4">
            <div id="default-schedule-calendar" data-schedules='@json($schedules)'
                data-save-url="{{ route('users.default-schedules.update', $user) }}"
                data-initial-date="{{ $initialDate }}" data-attendance-types='@json($attendanceTypesPayload)'
                data-default-attendance-type="{{ $defaultAttendanceTypeId }}"
                data-monday-label="{{ __('personnel.users_default_schedule_monday') }}"
                data-tuesday-label="{{ __('personnel.users_default_schedule_tuesday') }}"
                data-wednesday-label="{{ __('personnel.users_default_schedule_wednesday') }}"
                data-thursday-label="{{ __('personnel.users_default_schedule_thursday') }}"
                data-friday-label="{{ __('personnel.users_default_schedule_friday') }}"
                data-saturday-label="{{ __('personnel.users_default_schedule_saturday') }}"
                data-sunday-label="{{ __('personnel.users_default_schedule_sunday') }}"
                data-monday-short-label="{{ __('personnel.users_default_schedule_monday_short') }}"
                data-tuesday-short-label="{{ __('personnel.users_default_schedule_tuesday_short') }}"
                data-wednesday-short-label="{{ __('personnel.users_default_schedule_wednesday_short') }}"
                data-thursday-short-label="{{ __('personnel.users_default_schedule_thursday_short') }}"
                data-friday-short-label="{{ __('personnel.users_default_schedule_friday_short') }}"
                data-saturday-short-label="{{ __('personnel.users_default_schedule_saturday_short') }}"
                data-sunday-short-label="{{ __('personnel.users_default_schedule_sunday_short') }}">
            </div>

        </div>
    </div>

    <dialog id="schedule-modal" class="modal" data-title-add="{{ __('personnel.users_default_schedule_modal_add') }}"
        data-title-edit="{{ __('personnel.users_default_schedule_modal_edit') }}"
        data-error-end-before-start="{{ __('personnel.users_default_schedule_error_end_before_start') }}"
        data-error-save="{{ __('personnel.users_default_schedule_save_error') }}">
        <div class="modal-box">
            <h3 class="font-semibold text-lg mb-2">{{ __('personnel.users_default_schedule_modal_edit') }}</h3>
            <div class="flex flex-col gap-3">
                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_day') }}</span>
                    <select id="modal-day-select" class="select select-bordered w-full">
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
                    <input type="time" id="modal-hour-start" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_end') }}</span>
                    <input type="time" id="modal-hour-end" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_type') }}</span>
                    <select id="modal-type" class="select select-bordered w-full">
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
                    id="modal-cancel">{{ __('personnel.users_default_schedule_cancel') }}</button>
                <button class="btn btn-error"
                    id="modal-delete">{{ __('personnel.users_default_schedule_delete') }}</button>
                <button class="btn btn-primary"
                    id="modal-save">{{ __('personnel.users_default_schedule_save') }}</button>
            </div>
        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/user-default-schedule.js')
    @endpush
</x-layouts.app>
