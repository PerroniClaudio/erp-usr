<x-layouts.app :shouldHavePadding=false>
    @php
        $functionSectionsMenu = [
            ['id' => 'personal-data', 'label' => __('personnel.users_personal_data'), 'show' => true],
            ['id' => 'roles', 'label' => __('personnel.users_roles_label'), 'show' => $canManageRoles ?? false],
            [
                'id' => 'default-schedule',
                'label' => __('personnel.users_default_schedule_title'),
                'show' => true,
            ],
            [
                'id' => 'time-off',
                'label' => __('personnel.users_time_off_and_rol_management'),
                'show' => true,
            ],
            [
                'id' => 'residence-location',
                'label' => __('personnel.users_residence') . ' e ' . __('personnel.users_location'),
                'show' => true,
            ],
            ['id' => 'vehicles', 'label' => 'Automezzi', 'show' => true],
            ['id' => 'companies', 'label' => 'Aziende', 'show' => true],
            ['id' => 'groups', 'label' => 'Gruppi', 'show' => true],
        ];

        $functionSectionsMenu = array_values(array_filter($functionSectionsMenu, fn($section) => $section['show']));
    @endphp

    <div class="drawer lg:drawer-open">
        <input id="user-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col px-4 pb-16">
            <!-- Page content here -->
            <div class="container mx-auto flex p-4">
                <label for="user-drawer" class="btn btn-secondary drawer-button lg:hidden">
                    <x-lucide-menu class="h-6 w-6" />
                </label>
            </div>
            <main class="container mx-auto flex flex-col gap-4">


                <input type="hidden" name="user_id" id="user_id" value="{{ $user->id }}">

                <div class="flex justify-between items-center">
                    <h1 class="text-4xl">{{ __('personnel.users_edit_user') }}</h1>
                </div>

                <hr>

                <div id="user-functions" class="flex flex-col gap-4">
                    <section class="function-section flex flex-col gap-4" data-function-section="personal-data">
                        <div class="card bg-base-300 ">
                            <form class="card-body" method="POST" action="{{ route('users.update', $user) }}">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg">{{ __('personnel.users_personal_data') }}</h2>

                                    <div class="flex items-center gap-1">
                                        <div class="btn btn-primary" id="enable-edit-personal-data">
                                            <x-lucide-pencil class="h-4 w-4" />
                                        </div>
                                        <div class="btn btn-primary"
                                            onclick="document.getElementById('submit-button-personal-data').click()">
                                            <x-lucide-save class="h-4 w-4" />
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_title') }}</legend>
                                        <input type="text" name="title" class="input w-full form-input-activable"
                                            disabled value="{{ old('title', $user->title) }}"
                                            placeholder="{{ __('personnel.users_title') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_name') }}</legend>
                                        <input type="text" name="name" class="input w-full"
                                            value="{{ old('name', $user->name) }}"
                                            placeholder="{{ __('personnel.users_name') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset col-span-2">
                                        <legend class="fieldset-legend">{{ __('personnel.users_email') }}</legend>
                                        <input type="email" name="email" class="input w-full"
                                            value="{{ old('email', $user->email) }}"
                                            placeholder="{{ __('personnel.users_email') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_cfp') }}</legend>
                                        <input type="text" name="cfp" class="input w-full form-input-activable"
                                            disabled value="{{ old('cfp', $user->cfp) }}"
                                            placeholder="{{ __('personnel.users_cfp') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_birth_date') }}</legend>
                                        <input type="date" name="birth_date"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('birth_date', $user->birth_date) }}"
                                            placeholder="{{ __('personnel.users_birth_date') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_company_name') }}
                                        </legend>
                                        <input type="text" name="company_name"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('company_name', $user->company_name) }}"
                                            placeholder="{{ __('personnel.users_company_name') }}" />
                                    </fieldset>

                                    @php
                                        $selectedHeadquarterId = old(
                                            'headquarter_id',
                                            $user->headquarters->first()?->id,
                                        );
                                    @endphp
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">
                                            {{ __('personnel.users_headquarter') }}
                                            @if ($mainCompany)
                                                <span
                                                    class="text-xs text-base-content/60">({{ $mainCompany->name }})</span>
                                            @endif
                                        </legend>
                                        <select name="headquarter_id" class="select w-full form-input-activable"
                                            disabled>
                                            @if ($mainCompanyHeadquarters->isEmpty())
                                                <option value="">{{ __('personnel.users_headquarter_none') }}
                                                </option>
                                            @else
                                                <option value="">
                                                    {{ __('personnel.users_headquarter_placeholder') }}
                                                </option>
                                                @foreach ($mainCompanyHeadquarters as $hq)
                                                    <option value="{{ $hq->id }}"
                                                        {{ $selectedHeadquarterId == $hq->id ? 'selected' : '' }}>
                                                        {{ $hq->name }} — {{ $hq->city }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_vat_number') }}</legend>
                                        <input type="text" name="vat_number"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('vat_number', $user->vat_number) }}"
                                            placeholder="{{ __('personnel.users_vat_number') }}" />
                                    </fieldset>


                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_mobile_number') }}
                                        </legend>
                                        <input type="text" name="mobile_number"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('mobile_number', $user->mobile_number) }}"
                                            placeholder="{{ __('personnel.users_mobile_number') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_phone_number') }}
                                        </legend>
                                        <input type="text" name="phone_number"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('phone_number', $user->phone_number) }}"
                                            placeholder="{{ __('personnel.users_phone_number') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_weekly_hours') }}
                                        </legend>
                                        <input type="number" name="weekly_hours"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('weekly_hours', $user->weekly_hours) }}"
                                            placeholder="{{ __('personnel.users_weekly_hours') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_category') }}</legend>
                                        <select name="category" class="select w-full form-input-activable" disabled>
                                            <option value="Dipendente"
                                                {{ old('category', $user->category) == 'Dipendente' ? 'selected' : '' }}>
                                                {{ __('personnel.users_category_employee') }}
                                            </option>
                                            <option value="Consulente"
                                                {{ old('category', $user->category) == 'Consulente' ? 'selected' : '' }}>
                                                {{ __('personnel.users_category_consultant') }}
                                            </option>
                                            <option value="Stagista"
                                                {{ old('category', $user->category) == 'Stagista' ? 'selected' : '' }}>
                                                {{ __('personnel.users_category_intern') }}
                                            </option>
                                        </select>
                                    </fieldset>


                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_badge_code') }}
                                        </legend>
                                        <input type="text" name="badge_code"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('badge_code', $user->badge_code) }}"
                                            placeholder="{{ __('personnel.users_badge_code') }}" />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_employee_code') }}
                                        </legend>
                                        <input type="text" name="employee_code"
                                            class="input w-full form-input-activable" disabled
                                            value="{{ old('employee_code', $user->employee_code) }}"
                                            placeholder="{{ __('personnel.users_employee_code') }}" />
                                    </fieldset>

                                    @if ($canManageRoles)
                                        <fieldset class="fieldset col-span-2">
                                            <legend class="fieldset-legend">
                                                {{ __('personnel.users_business_trips_access') }}</legend>
                                            <label class="label cursor-pointer justify-start gap-3">
                                                <input type="checkbox" name="business_trips_access" value="1"
                                                    class="toggle toggle-primary"
                                                    {{ old('business_trips_access', $user->hasRole('admin') || $user->can('business-trips.access')) ? 'checked' : '' }}>
                                                <span
                                                    class="label-text">{{ __('personnel.users_business_trips_access_help') }}</span>
                                            </label>
                                        </fieldset>
                                    @endif

                                    <button id="submit-button-personal-data" type="submit" class="hidden"></button>
                                </div>
                            </form>
                        </div>
                    </section>

                    @if ($canManageRoles)
                        @php
                            $assignedRoles = collect($user->getRoleNames());
                            $availableRolesForUser = collect($availableRoles)->pluck('name')->diff($assignedRoles);
                            $badgeColors = [
                                'admin' => 'badge-error',
                                'Responsabile HR' => 'badge-warning',
                                'Operatore HR' => 'badge-info',
                                'standard' => 'badge-success',
                            ];
                        @endphp
                        <section class="function-section flex flex-col gap-4" data-function-section="roles">
                            <div class="card bg-base-300">
                                <div class="card-body flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-lg">{{ __('personnel.users_roles_label') }}</h2>

                                        <button type="button" class="btn btn-primary open-role-modal"
                                            data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                            data-assigned='@json($assignedRoles->values())'
                                            data-available='@json($availableRolesForUser->values())'>
                                            {{ __('personnel.users_roles_manage') }}
                                        </button>
                                    </div>
                                    <hr>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($assignedRoles as $roleName)
                                            <span
                                                class="badge {{ $badgeColors[$roleName] ?? 'badge-ghost' }}">{{ $roleName }}</span>
                                        @empty
                                            <span
                                                class="text-base-content/60 text-sm italic">{{ __('personnel.users_roles_none_assigned') }}</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif

                    @php
                        $defaultScheduleItems = old(
                            'schedule',
                            $user->defaultSchedules
                                ->map(
                                    fn($item) => [
                                        'day' => $item->day,
                                        'hour_start' =>
                                            $item->hour_start instanceof \Illuminate\Support\Carbon
                                                ? $item->hour_start->format('H:i')
                                                : $item->hour_start,
                                        'hour_end' =>
                                            $item->hour_end instanceof \Illuminate\Support\Carbon
                                                ? $item->hour_end->format('H:i')
                                                : $item->hour_end,
                                        'type' => $item->type,
                                    ],
                                )
                                ->toArray(),
                        );
                    @endphp

                    <section class="function-section flex flex-col gap-4" data-function-section="default-schedule">
                        <div class="card bg-base-300">
                            <div class="card-body flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg">{{ __('personnel.users_default_schedule_title') }}</h2>
                                    <a href="{{ route('users.default-schedules.calendar', $user) }}"
                                        class="btn btn-primary">
                                        {{ __('personnel.users_default_schedule_view') }}
                                    </a>
                                </div>
                                <hr>
                                <p class="text-sm ">
                                    {{ __('personnel.users_default_schedule_edit_intro', ['name' => $user->name]) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="function-section flex flex-col gap-4" data-function-section="time-off">
                        <x-users.time-off :user="$user" />
                    </section>

                    <section class="function-section flex flex-col gap-4" data-function-section="residence-location">
                        <div class="card bg-base-300 ">
                            <div class="card-body">
                                <div class="card-title flex items-center justify-between gap-2">
                                    <span>{{ __('personnel.users_residence') }}</span>

                                    <div class="btn btn-primary" id="submit-button-residence">
                                        <x-lucide-save class="h-4 w-4" />
                                    </div>
                                </div>

                                <hr>

                                <div>
                                    <div class="join w-full">
                                        <div class="flex-1">
                                            <label class="input validator join-item w-full">
                                                <input type="text" id="address-search-input"
                                                    placeholder="Cerca indirizzo" />
                                            </label>
                                        </div>
                                        <button class="btn btn-primary join-item"
                                            id="validate-address-button">Convalida</button>
                                    </div>
                                </div>

                                <p class="text-error label residence-error"></p>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                                        <input type="text" name="address" class="input w-full residence-form"
                                            value="{{ old('address', $user->address) }}"
                                            placeholder="{{ __('personnel.users_address') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}
                                        </legend>
                                        <input type="text" name="street_number"
                                            class="input w-full residence-form"
                                            value="{{ old('street_number', $user->street_number) }}"
                                            placeholder="{{ __('personnel.users_street_number') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                                        <input type="text" name="city" class="input w-full residence-form"
                                            value="{{ old('city', $user->city) }}"
                                            placeholder="{{ __('personnel.users_city') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}
                                        </legend>
                                        <input type="text" name="postal_code" class="input w-full residence-form"
                                            value="{{ old('postal_code', $user->postal_code) }}"
                                            placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                                        <input type="text" name="province" class="input w-full residence-form"
                                            value="{{ old('province', $user->province) }}"
                                            placeholder="{{ __('personnel.users_province') }}" disabled />
                                    </fieldset>

                                    <div></div>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                                        <input type="text" name="latitude" class="input w-full residence-form"
                                            value="{{ old('latitude', $user->latitude) }}"
                                            placeholder="{{ __('personnel.users_latitude') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}</legend>
                                        <input type="text" name="longitude" class="input w-full residence-form"
                                            value="{{ old('longitude', $user->longitude) }}"
                                            placeholder="{{ __('personnel.users_longitude') }}" disabled />
                                    </fieldset>
                                </div>

                            </div>
                        </div>

                        <div class="card bg-base-300 ">
                            <div class="card-body">

                                <div class="card-title flex items-center justify-between gap-2">
                                    <span>{{ __('personnel.users_location') }}</span>

                                    <div class="btn btn-primary" id="submit-button-location">
                                        <x-lucide-save class="h-4 w-4" />
                                    </div>
                                </div>

                                <hr>

                                <div>
                                    <div class="join w-full">
                                        <div class="flex-1">
                                            <label class="input validator join-item w-full">
                                                <input type="text" id="location-address-search-input"
                                                    placeholder="Cerca indirizzo" />
                                            </label>
                                        </div>
                                        <button class="btn btn-primary join-item"
                                            id="validate-location-address-button">Convalida</button>
                                    </div>
                                </div>

                                <p class="text-error label location-error"></p>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_address') }}</legend>
                                        <input type="text" name="address" class="input w-full location-form"
                                            value="{{ old('location_address', $user->location_address) }}"
                                            placeholder="{{ __('personnel.users_address') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_street_number') }}
                                        </legend>
                                        <input type="text" name="street_number" class="input w-full location-form"
                                            value="{{ old('location_street_number', $user->location_street_number) }}"
                                            placeholder="{{ __('personnel.users_street_number') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_city') }}</legend>
                                        <input type="text" name="city" class="input w-full location-form"
                                            value="{{ old('location_city', $user->location_city) }}"
                                            placeholder="{{ __('personnel.users_city') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_postal_code') }}
                                        </legend>
                                        <input type="text" name="postal_code" class="input w-full location-form"
                                            value="{{ old('location_postal_code', $user->location_postal_code) }}"
                                            placeholder="{{ __('personnel.users_postal_code') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_province') }}</legend>
                                        <input type="text" name="province" class="input w-full location-form"
                                            value="{{ old('location_province', $user->location_province) }}"
                                            placeholder="{{ __('personnel.users_province') }}" disabled />
                                    </fieldset>

                                    <div></div>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_latitude') }}</legend>
                                        <input type="text" name="latitude" class="input w-full location-form"
                                            value="{{ old('location_latitude', $user->location_latitude) }}"
                                            placeholder="{{ __('personnel.users_latitude') }}" disabled />
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend">{{ __('personnel.users_longitude') }}
                                        </legend>
                                        <input type="text" name="longitude" class="input w-full location-form"
                                            value="{{ old('location_longitude', $user->location_longitude) }}"
                                            placeholder="{{ __('personnel.users_longitude') }}" disabled />
                                    </fieldset>
                                </div>

                            </div>
                        </div>
                    </section>

                    <section class="function-section flex flex-col gap-4" data-function-section="vehicles">
                        <x-users.vehicles :user="$user" />
                    </section>

                    <section class="function-section flex flex-col gap-4" data-function-section="companies">
                        <x-users.companies :user="$user" />
                    </section>

                    <section class="function-section flex flex-col gap-4" data-function-section="groups">
                        <x-users.groups :user="$user" />
                    </section>
                </div>

                @if (($canManageRoles ?? false) && isset($badgeColors))
                    <dialog id="roles-modal" class="modal" data-badge-colors='@json($badgeColors)'
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
                                            <h4 class="font-semibold">{{ __('personnel.users_roles_available') }}
                                            </h4>
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
                @endif

            </main>
        </div>
        <div class="drawer-side z-50">
            <label for="user-drawer" class="drawer-overlay" aria-label="Chiudi il menu"></label>
            <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4 gap-2">
                @foreach ($functionSectionsMenu as $section)
                    <li>
                        <button type="button" class="btn btn-ghost justify-start w-full"
                            data-function-target="{{ $section['id'] }}">
                            {{ $section['label'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>


    @push('scripts')
        @vite('resources/js/users.js')
        @if ($canManageRoles ?? false)
            @vite('resources/js/roles.js')
        @endif
    @endpush


</x-layouts.app>
