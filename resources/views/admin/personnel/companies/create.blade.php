<x-layouts.app>

    <x-layouts.header :title="__('personnel.companies_new_company')">
        <x-slot:actions>
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('personnel.companies_save') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="card bg-base-300 ">
        <form class="card-body gap-4" method="POST" action="{{ route('companies.store') }}">
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
