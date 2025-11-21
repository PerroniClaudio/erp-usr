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
