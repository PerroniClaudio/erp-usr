<x-layouts.app>

    <div class="flex justify-between items-center">
        <h1 class="text-4xl">{{ __('personnel.groups_new_group') }}</h1>
        <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
            {{ __('personnel.groups_save') }}
        </a>
    </div>

    <hr>

    <div class="card bg-base-300 ">
        <form class="card-body" method="POST" action="{{ route('groups.store') }}">
            @csrf
            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('personnel.groups_name') }}</legend>
                <input type="text" name="name" class="input" value="{{ old('name') }}"
                    placeholder="{{ __('personnel.groups_name') }}" />
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">{{ __('personnel.groups_email') }}</legend>
                <input type="email" name="email" class="input" value="{{ old('email') }}"
                    placeholder="{{ __('personnel.groups_email') }}" />
            </fieldset>

            <button id="submit-button" type="submit" class="hidden">{{ __('personnel.groups_save') }}</button>
        </form>
    </div>


</x-layouts.app>
