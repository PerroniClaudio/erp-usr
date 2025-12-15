<x-layouts.app>
    <x-layouts.header :title="__('personnel.users_scheduled_time_off_title')">
        <x-slot:actions>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                {{ __('personnel.users_scheduled_time_off_back') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div id="scheduled-time-off-toast" class="toast toast-bottom toast-end hidden">
        <div class="alert alert-success">
            <x-lucide-check class="w-4 h-4" />
            <span>{{ __('personnel.users_scheduled_time_off_save_success') }}</span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-base-content/70">
            {{ __('personnel.users_scheduled_time_off_intro', ['name' => $user->name]) }}
        </p>
        <div class="flex gap-2">
            <button class="btn btn-secondary" id="add-timeoff-slot">
                {{ __('personnel.users_scheduled_time_off_add_slot') }}
            </button>
            <button class="btn btn-primary" id="save-scheduled-time-off">
                {{ __('personnel.users_scheduled_time_off_save') }}
            </button>
        </div>
    </div>

    <div class="card bg-base-300">
        <div class="card-body flex flex-col gap-4">
            <div id="scheduled-time-off-calendar" data-schedules='@json($scheduledEntriesPayload)'
                data-save-url="{{ route('users.scheduled-time-off.update', $user) }}"
                data-initial-date="{{ $initialDate }}"
                data-time-off-types='@json($timeOffTypesPayload)'
                data-default-time-off-type="{{ $defaultTimeOffTypeId }}"
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

    <dialog id="scheduled-time-off-modal" class="modal"
        data-title-add="{{ __('personnel.users_scheduled_time_off_modal_add') }}"
        data-title-edit="{{ __('personnel.users_scheduled_time_off_modal_edit') }}"
        data-error-end-before-start="{{ __('personnel.users_scheduled_time_off_error_end_before_start') }}"
        data-error-save="{{ __('personnel.users_scheduled_time_off_save_error') }}">
        <div class="modal-box">
            <h3 class="font-semibold text-lg mb-2">{{ __('personnel.users_scheduled_time_off_modal_edit') }}</h3>
            <div class="flex flex-col gap-3">
                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_day') }}</span>
                    <select id="scheduled-modal-day-select" class="select select-bordered w-full">
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
                    <input type="time" id="scheduled-modal-hour-start" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_default_schedule_end') }}</span>
                    <input type="time" id="scheduled-modal-hour-end" class="input input-bordered w-full" />
                </label>

                <label class="form-control">
                    <span class="label-text text-sm">{{ __('personnel.users_time_off') }}</span>
                    <select id="scheduled-modal-type" class="select select-bordered w-full">
                        @foreach ($timeOffTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="modal-action">
                <button class="btn btn-ghost"
                    id="scheduled-modal-cancel">{{ __('personnel.users_default_schedule_cancel') }}</button>
                <button class="btn btn-error"
                    id="scheduled-modal-delete">{{ __('personnel.users_default_schedule_delete') }}</button>
                <button class="btn btn-primary"
                    id="scheduled-modal-save">{{ __('personnel.users_scheduled_time_off_save') }}</button>
            </div>
        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/user-scheduled-time-off.js')
    @endpush
</x-layouts.app>
