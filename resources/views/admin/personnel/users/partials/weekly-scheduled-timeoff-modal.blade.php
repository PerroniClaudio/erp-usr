<dialog id="weekly-timeoff-modal" class="modal"
    data-error-end-before-start="{{ __('personnel.users_scheduled_time_off_error_end_before_start') }}"
    data-error-save="{{ __('personnel.users_scheduled_time_off_save_error') }}">
    <div class="modal-box">
        <h3 class="font-semibold text-lg mb-2" data-modal-title>
            {{ __('personnel.users_scheduled_time_off_modal_edit') }}
        </h3>
        <div class="flex flex-col gap-3">
            <label class="form-control">
                <span class="label-text text-sm">{{ __('personnel.users_default_schedule_day') }}</span>
                <select id="weekly-timeoff-day" class="select select-bordered w-full">
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
                <input type="time" id="weekly-timeoff-hour-start" class="input input-bordered w-full" />
            </label>

            <label class="form-control">
                <span class="label-text text-sm">{{ __('personnel.users_default_schedule_end') }}</span>
                <input type="time" id="weekly-timeoff-hour-end" class="input input-bordered w-full" />
            </label>

            <label class="form-control">
                <span class="label-text text-sm">{{ __('personnel.users_time_off') }}</span>
                <select id="weekly-timeoff-type" class="select select-bordered w-full">
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
                id="weekly-timeoff-cancel">{{ __('personnel.users_default_schedule_cancel') }}</button>
            <button class="btn btn-error"
                id="weekly-timeoff-delete">{{ __('personnel.users_default_schedule_delete') }}</button>
            <button class="btn btn-primary"
                id="weekly-timeoff-save">{{ __('personnel.users_scheduled_time_off_save') }}</button>
        </div>
    </div>
</dialog>
