<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.companies_new_company') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('personnel.companies_save') }}
        </a>
    </div>

    <hr>

    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('companies.store') }}">
            @csrf
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('personnel.companies_name') }}</legend>
                <input type="text" name="name" class="input" value="{{ old('name') }}"
                    placeholder="{{ __('personnel.companies_name') }}" />
            </fieldset>


            <button id="submit-button" type="submit" class="hidden">{{ __('personnel.companies_save') }}</button>
        </form>
    </div>


</x-layouts.app>
