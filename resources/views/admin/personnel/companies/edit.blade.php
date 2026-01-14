<x-layouts.app>

    <input type="hidden" name="company_id" id="company_id" value="{{ $company->id }}">

    <x-layouts.header :title="__('personnel.companies_edit_company')">
        <x-slot:actions>
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('personnel.companies_save') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="flex flex-col gap-4">

        <div class="card bg-base-300 ">
            <form class="card-body gap-4" method="POST" action="{{ route('companies.update', $company) }}">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl">{{ __('personnel.companies_data_title') }}</h2>
                </div>
                <hr>
                @csrf
                @method('PUT')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">{{ __('personnel.companies_name') }}</legend>
                    <input type="text" name="name" class="input" value="{{ old('name', $company->name) }}"
                        placeholder="{{ __('personnel.companies_name') }}" />
                </fieldset>



                <button id="submit-button" type="submit" class="hidden">{{ __('personnel.companies_save') }}</button>
            </form>
        </div>

        <div class="card bg-base-300 ">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl">{{ __('personnel.companies_users') }}</h2>
                    <div class="btn btn-primary" id="associate-users-modal-opener" onclick="associate_user.showModal()">
                        <x-lucide-plus class="h-4 w-4" />
                    </div>
                </div>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ __('personnel.companies_personnel_name') }}</th>
                            <th>{{ __('personnel.companies_personnel_email') }}</th>
                            <th>{{ __('personnel.companies_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company->users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>

                                <td>
                                    <form action="{{ route('companies.users.dissociate', [$company, $user]) }}"
                                        method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-warning">
                                            <x-lucide-trash-2 class="h-4 w-4" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <div class="card bg-base-300">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl">{{ __('headquarters.company_headquarters') }}</h2>
                    <a href="{{ route('headquarters.create', ['company_id' => $company->id]) }}"
                        class="btn btn-primary">
                        <x-lucide-plus class="h-4 w-4" />
                    </a>
                </div>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <td>{{ __('headquarters.name') }}</td>
                            <td>{{ __('headquarters.address') }}</td>
                            <td>{{ __('headquarters.actions') }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @unless ($company->headquarters->count())
                            <tr>
                                <td colspan="3" class="text-center">
                                    {{ __('headquarters.no_headquarters') }}
                                </td>
                            </tr>
                        @else
                            @foreach ($company->headquarters as $headquarter)
                                <tr>
                                    <td>{{ $headquarter->name }}</td>
                                    <td>{{ $headquarter->address }}, {{ $headquarter->city }},
                                        {{ $headquarter->province }}, {{ $headquarter->zip_code }}</td>
                                    <td>
                                        <a href="{{ route('headquarters.edit', $headquarter->id) }}"
                                            class="btn btn-primary">
                                            <x-lucide-edit-2 class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endunless
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <dialog id="associate_user" class="modal">
        <div class="modal-box min-w-3/4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('personnel.companies_associate_users') }}</h3>
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
                        <div class="overflow-x-auto overflow-y-auto max-h-96">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('personnel.companies_personnel_name') }}</th>
                                        <th>{{ __('personnel.companies_personnel_email') }}</th>
                                        <th>{{ __('personnel.companies_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="user-list">
                                    <template id="user-list-item-template">
                                        <tr class="user-row" data-key="0">
                                            <td class="user-id-field"></td>
                                            <td class="user-name-field"></td>
                                            <td class="user-email-field"></td>
                                            <td class="user-action-field">
                                                <div class="btn btn-primary add-user-button" data-user-id="">
                                                    <x-lucide-plus class="h-4 w-4" />
                                                </div>
                                            </td>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-300 ">
                    <div class="card-body">
                        <div class="overflow-x-auto overflow-y-auto max-h-96">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('personnel.companies_personnel_name') }}</th>
                                        <th>{{ __('personnel.companies_personnel_email') }}</th>
                                        <th>{{ __('personnel.companies_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="selected-user-list">
                                    <template id="selected-user-list-item-template">
                                        <tr class="user-row" data-key="0">
                                            <td class="user-id-field"></td>
                                            <td class="user-name-field"></td>
                                            <td class="user-email-field"></td>
                                            <td class="user-action-field">
                                                <div class="btn btn-primary remove-user-button" data-user-id="">
                                                    <x-lucide-minus class="h-4 w-4" />
                                                </div>
                                            </td>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal-action">
                <button type="submit" class="btn btn-primary"
                    id="save-association">{{ __('personnel.companies_save') }}</button>
            </div>



        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/companies.js')
    @endpush


</x-layouts.app>
