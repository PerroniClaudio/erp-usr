@props([
    'user' => null,
])

<template id="group-list-item-template">
    <tr class="group-row" data-key="0">
        <td class="group-name-field"></td>
        <td class="group-action-field">
            <div class="btn btn-primary add-group-button" data-group-id="">
                <x-lucide-plus class="h-4 w-4" />
            </div>
        </td>
    </tr>
</template>

<template id="group-added-list-item-template">
    <tr class="group-row" data-key="0">
        <td class="group-name-field"></td>
        <td class="group-action-field">
            <div class="btn btn-primary remove-group-button" data-group-id="">
                <x-lucide-minus class="h-4 w-4" />
            </div>
        </td>
    </tr>
</template>

<template id="group-list-none-available-template">
    <tr class="group-row" data-key="0">
        <td colspan="2" class="text-center">
            {{ __('personnel.users_associated_groups_no_groups_available') }}
        </td>
    </tr>

</template>

<div class="card bg-base-300 max-w-full">
    <div class="card-body">

        <div class="flex items-center justify-between">
            <h2 class="card-title">{{ __('personnel.users_associated_groups') }}</h2>
            </h2>
            <a class="btn btn-primary" id="associate-groups-users-modal-opener">
                <x-lucide-plus class="h-4 w-4" />
            </a>
        </div>

        <hr>

        <table class="table w-full">
            <thead>
                <tr>
                    <th>{{ __('personnel.users_associated_groups_name') }}</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody class="overflow-y-auto">
                @unless ($user->groups->count())
                    <tr>
                        <td colspan="5" class="text-center">
                            {{ __('personnel.users_associated_groups_no_groups') }}
                        </td>
                    </tr>
                @endunless
                @foreach ($user->groups as $group)
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td>


                            <form
                                action="{{ route('users.group.destroy', [
                                    'user' => $user->id,
                                    'group' => $group->id,
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


<dialog id="groups_modal" class="modal">
    <div class="modal-box min-w-3/4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"> {{ __('personnel.users_associated_groups_associate_group') }}</h3>
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
                                <th>{{ __('personnel.users_associated_groups_name') }}</th>
                                <th>{{ __('personnel.users_associated_groups_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="associate-groups-users-table-body">
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
                                    <th>{{ __('personnel.users_associated_groups_name') }}</th>
                                    <th>{{ __('personnel.users_associated_groups_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="associated-groups-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <button class="btn btn-primary" id="save-groups-button">{{ __('personnel.groups_save') }}</button>
        </div>
    </div>
</dialog>

@push('scripts')
    @vite('resources/js/user_groups.js')
@endpush
