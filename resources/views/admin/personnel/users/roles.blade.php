<x-layouts.app>
    @php
        $badgeColors = [
            'admin' => 'badge-error', // rosso
            'Responsabile HR' => 'badge-warning', // giallo
            'Operatore HR' => 'badge-info', // azzurro
            'standard' => 'badge-success', // verde
        ];
    @endphp

    @push('scripts')
        @vite('resources/js/roles.js')
    @endpush

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-3xl font-bold">{{ __('personnel.users_roles_management') }}</h1>
    </div>

    <hr>

    <div class="card bg-base-300">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('personnel.users_roles_user_column') }}</th>
                            <th>{{ __('personnel.users_roles_label') }}</th>
                            <th class="w-32">{{ __('personnel.users_roles_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            @php
                                $assignedRoles = collect($user->getRoleNames());
                                $available = collect($availableRoles)->pluck('name')->diff($assignedRoles);
                            @endphp
                            <tr>
                                <td class="font-medium">{{ $user->name }}</td>
                                <td class="flex flex-wrap gap-2">
                                    @forelse ($assignedRoles as $role)
                                        <span class="badge {{ $badgeColors[$role] ?? 'badge-ghost' }}">{{ $role }}</span>
                                    @empty
                                        <span class="text-base-content/60 text-sm italic">{{ __('personnel.users_roles_none') }}</span>
                                    @endforelse
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary open-role-modal"
                                        data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                        data-assigned='@json($assignedRoles->values())'
                                        data-available='@json($available->values())'>
                                        {{ __('personnel.users_roles_manage_action') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <dialog id="roles-modal" class="modal"
        data-badge-colors='@json($badgeColors)'
        data-title-template="{{ __('personnel.users_roles_modal_title', ['name' => ':name']) }}"
        data-error-message="{{ __('personnel.users_roles_modal_error') }}"
        data-empty-available="{{ __('personnel.users_roles_none_available') }}"
        data-empty-assigned="{{ __('personnel.users_roles_none_assigned') }}">
        <div class="modal-box max-w-4xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold" id="roles-modal-title"></h3>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle">✕</button>
                </form>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="card bg-base-200">
                    <div class="card-body gap-2">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold">{{ __('personnel.users_roles_available') }}</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <tbody id="available-roles-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-200">
                    <div class="card-body gap-2">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold">{{ __('personnel.users_roles_assigned') }}</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <tbody id="assigned-roles-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>

</x-layouts.app>
