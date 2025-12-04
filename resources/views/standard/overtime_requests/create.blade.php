<x-layouts.app>
    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('overtime_requests.new_request') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('overtime_requests.save_request') }}
        </a>
    </div>
    <hr>
    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('overtime-requests.store') }}">
            @csrf
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
                @php
                    $defaultOvertimeTypeId = old('overtime_type_id', optional($overtimeTypes->firstWhere('acronym', 'ST'))->id);
                @endphp
                <select class="select" name="overtime_type_id" value="{{ $defaultOvertimeTypeId }}">
                    @foreach ($overtimeTypes as $type)
                        <option value="{{ $type->id }}"
                            {{ $defaultOvertimeTypeId == $type->id ? 'selected' : '' }}>
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
