<x-layouts.app>


    <div class="card bg-base-300">
        <div class="card-body">
            <h3 class="card-title">{{ __('time_off_requests.new_request') }}</h3>
            <hr>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data inizio</legend>
                <input type="date" name="date_from" class="input" value="{{ old('date_from') }}" />
            </fieldset>
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Data fine</legend>
                <input type="date" name="date_to" class="input" value="{{ old('date_to') }}" />
            </fieldset>

        </div>
    </div>

</x-layouts.app>
