<x-layouts.app>

    <input type="hidden" name="group_id" id="group_id" value="{{ $group->id }}">

    <x-layouts.header :title="__('personnel.groups_edit_group')">
        <x-slot:actions>
            <a class="btn btn-primary" onclick="document.getElementById('submit-button').click()">
                {{ __('personnel.groups_save') }}
            </a>
        </x-slot:actions>
    </x-layouts.header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="col-span-1 lg:col-span-1">
            <div class="card bg-base-300 ">
                <form class="card-body" method="POST" action="{{ route('groups.update', $group) }}">
                    @csrf
                    @method('PUT')
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.groups_name') }}</legend>
                        <input type="text" name="name" class="input" value="{{ old('name', $group->name) }}"
                            placeholder="{{ __('personnel.groups_name') }}" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">{{ __('personnel.groups_email') }}</legend>
                        <input type="email" name="email" class="input" value="{{ old('email', $group->email) }}"
                            placeholder="{{ __('personnel.groups_email') }}" />
                    </fieldset>

                    <button id="submit-button" type="submit" class="hidden">{{ __('personnel.groups_save') }}</button>
                </form>
            </div>
        </div>

        <div class="col-span-1 lg:col-span-3">
            <div class="card bg-base-300 ">
                <div class="card-body">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl">{{ __('personnel.groups_users') }}</h2>
                        <div class="btn btn-primary" id="associate-users-modal-opener"
                            onclick="associate_user.showModal()">
                            <x-lucide-plus class="h-4 w-4" />
                        </div>
                    </div>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('personnel.groups_personnel_name') }}</th>
                                <th>{{ __('personnel.groups_personnel_email') }}</th>
                                <th>{{ __('personnel.groups_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group->users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>

                                    <td>
                                        <form action="{{ route('groups.users.dissociate', [$group, $user]) }}"
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
        </div>
    </div>

    <dialog id="associate_user" class="modal">
        <div class="modal-box min-w-3/4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"> {{ __('personnel.groups_associate_users') }}</h3>
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
                                        <th>{{ __('personnel.groups_personnel_name') }}</th>
                                        <th>{{ __('personnel.groups_personnel_email') }}</th>
                                        <th>{{ __('personnel.groups_actions') }}</th>
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
                                        <th>{{ __('personnel.groups_personnel_name') }}</th>
                                        <th>{{ __('personnel.groups_personnel_email') }}</th>
                                        <th>{{ __('personnel.groups_actions') }}</th>
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
                    id="save-association">{{ __('personnel.groups_save') }}</button>
            </div>



        </div>
    </dialog>

    @push('scripts')
        @vite('resources/js/groups.js')
    @endpush


</x-layouts.app>
