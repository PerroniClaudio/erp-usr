<div>
    <!-- Act only according to that maxim whereby you can, at the same time, will that it should become a universal law. - Immanuel Kant -->
</div>
<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('overtime_requests.new_request') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('overtime_requests.save_request') }}
        </a>
    </div>
    <hr>
    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('admin.overtime-requests.store') }}">
            @csrf
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('time_off_requests.select_user') }}</legend>
                <select id="user_id" name="user_id" class="select">
                    <option value="">{{ __('time_off_requests.choose_user') }}</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.company') }}</legend>
                <select class="select" name="company_id" value="{{ old('company_id') }}">
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.date') }}</legend>
                <input type="date" name="date" class="input"
                    value="{{ old('date', \Carbon\Carbon::today()->toDateString()) }}"
                    placeholder="{{ \Carbon\Carbon::today()->toDateString() }}" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.time_in') }}</legend>
                <input type="time" name="time_in" class="input" placeholder="00:00" value="{{ old('time_in') }}" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.time_out') }}</legend>
                <input type="time" name="time_out" class="input" placeholder="00:00"
                    value="{{ old('time_out') }}" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('overtime_requests.type') }}</legend>
                <select class="select" name="overtime_type_id" value="{{ old('overtime_type_id') }}">
                    @foreach ($overtimeTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ old('overtime_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->acronym }})
                        </option>
                    @endforeach
                </select>
            </fieldset>
            <button id="submit-button" type="submit"
                class="hidden">{{ __('overtime_requests.save_request') }}</button>
        </form>
    </div>
</x-layouts.app>
