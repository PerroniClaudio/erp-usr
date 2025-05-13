@props([
    'user' => null,
])

<template id="company-list-item-template">
    <tr class="company-row" data-key="0">
        <td class="company-name-field"></td>
        <td class="company-action-field">
            <div class="btn btn-primary add-company-button" data-company-id="">
                <x-lucide-plus class="h-4 w-4" />
            </div>
        </td>
    </tr>
</template>

<template id="company-added-list-item-template">
    <tr class="company-row" data-key="0">
        <td class="company-name-field"></td>
        <td class="company-action-field">
            <div class="btn btn-primary remove-company-button" data-company-id="">
                <x-lucide-minus class="h-4 w-4" />
            </div>
        </td>
    </tr>
</template>

<template id="company-list-none-available-template">
    <tr class="company-row" data-key="0">
        <td colspan="2" class="text-center">
            {{ __('personnel.users_associated_companies_no_companies_available') }}
        </td>
    </tr>

</template>

<div class="card bg-base-300 max-w-full col-span-2">
    <div class="card-body">

        <div class="flex items-center justify-between">
            <h2 class="card-title">{{ __('personnel.users_associated_companies') }}</h2>
            </h2>
            <a class="btn btn-primary" id="associate-users-modal-opener">
                <x-lucide-plus class="h-4 w-4" />
            </a>
        </div>

        <hr>

        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('personnel.users_associated_companies_name') }}</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody class="overflow-y-auto">
                @unless ($user->companies->count())
                    <tr>
                        <td colspan="5" class="text-center">
                            {{ __('personnel.users_associated_companies_no_companies') }}
                        </td>
                    </tr>
                @endunless
                @foreach ($user->companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>
                        <td>


                            <form
                                action="{{ route('users.company.destroy', [
                                    'user' => $user->id,
                                    'company' => $company->id,
                                ]) }}"
                                method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')



                                <button type="submit" class="btn btn-sm btn-warning">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>


<dialog id="associate_users_modal" class="modal">
    <div class="modal-box min-w-3/4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"> {{ __('personnel.users_associated_companies_associate_company') }}</h3>
            <form method="dialog">
                <!-- if there is a button in form, it will close the modal -->
                <button class="btn btn-ghost">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </form>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="card bg-base-300 ">
                <div class="card-body">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('personnel.users_associated_companies_name') }}</th>
                                <th>{{ __('personnel.users_associated_companies_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="associate-users-table-body">
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="card bg-base-300 ">
                <div class="card-body">
                    <div class="overflow-x-auto overflow-y-auto max-h-96">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('personnel.users_associated_companies_name') }}</th>
                                    <th>{{ __('personnel.users_associated_companies_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="associated-companies-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <button class="btn btn-primary" id="save-companies-button">{{ __('personnel.companies_save') }}</button>
        </div>
    </div>
</dialog>

@push('scripts')
    @vite('resources/js/user_aziende.js')
@endpush
