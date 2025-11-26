<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Presenze iFortech</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ route('favicon') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ route('favicon') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="antialiased">
    <div class="drawer lg:drawer-open">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
        <div class="drawer-content flex flex-col">
            <!-- Page content here -->
            <div class="container mx-auto flex p-4">
                <label for="my-drawer-2" class="btn btn-outline border-base-200 drawer-button lg:hidden">
                    <x-lucide-menu class="h-6 w-6" />
                </label>
            </div>
            <main class="container mx-auto flex flex-col gap-4 px-4 pb-16">
                {{ $slot }}
            </main>
        </div>
        <div class="drawer-side z-50">
            <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-300 text-base-content min-h-full w-80 p-4 ">
                <li class="border-b-2 border-base-100 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <div
                            class="bg-primary w-8 aspect-square rounded-full flex flex-col justify-center items-center text-primary-content font-bold">
                            @php
                                $initials = collect(explode(' ', auth()->user()->name))
                                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                    ->take(2)
                                    ->implode('');

                                echo $initials[0] . ($initials[1] ?? '');
                            @endphp
                        </div>
                        <span>
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                </li>
                <!-- Sidebar content here -->
                @php
                    $loggedUser = auth()->user();
                    $isAdmin = $loggedUser?->hasRole('admin');
                    $isHrManager = $loggedUser?->hasRole('Responsabile HR');
                    $isHrOperator = $loggedUser?->hasRole('Operatore HR');
                    $isHrUser = $isHrManager || $isHrOperator;
                    $canAccessBusinessTrips = $isAdmin || $loggedUser?->can('business-trips.access');
                @endphp
                @if ($isAdmin)
                    <li>
                        <a href="{{ route('admin.home') }}">
                            <div class="flex items-center">
                                <x-lucide-home class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.home') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <details>
                            <summary>
                                <x-lucide-contact class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.personnel') }}</span>
                            </summary>
                            <ul>
                                <li>
                                    <a href="{{ route('users.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-circle-user class="h-4 w-4 text-primary" />
                                            <span class="ml-2">{{ __('navbar.personnel_users') }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('groups.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-users class="h-4 w-4 text-primary" />
                                            <span class="ml-2">{{ __('navbar.personnel_groups') }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('companies.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-building class="h-4 w-4 text-primary" />
                                            <span class="ml-2">{{ __('navbar.personnel_companies') }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('users.roles') }}">
                                        <div class="flex items-center">
                                            <x-lucide-badge-check class="h-4 w-4 text-primary" />
                                            <span class="ml-2">Ruoli utenti</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                    <li>
                        <a href="{{ route('admin.attendances.index') }}">
                            <x-lucide-calendar class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.attendances') }}</span>
                        </a>
                    </li>
                    <li>
                        @if ($canAccessBusinessTrips)
                            <a href="{{ route('business-trips.index') }}">
                                <x-lucide-car class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.business_trips') }}</span>
                            </a>
                        @endif
                    </li>
                    <li>
                        <a href="{{ route('admin.time-off.index') }}">
                            <x-lucide-sun class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.time_off_requests') }}</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.overtime-requests.index') }}">

                            <x-lucide-clock class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.overtime_requests') }}</span>

                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.user-schedule-requests.index') }}">
                            <x-lucide-calendar-clock class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.schedule_requests_admin') }}</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.announcements.index') }}">
                            <x-lucide-megaphone class="h-6 w-6 text-primary" />
                            <span class="ml-2">Annunci</span>
                        </a>
                    </li>

                    <li>
                        <details>
                            <summary>
                                <x-lucide-user class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.personnel_functions') }}</span>
                            </summary>
                            <ul>
                                <li>
                                    <a href="{{ route('standard.profile.edit') }}">
                                        <div class="flex items-center">
                                            <x-lucide-circle-user class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.profile') }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('attendances.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-calendar class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.attendances') }}</span>
                                        </div>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('time-off-requests.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-sun class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.time_off') }}</span>
                                        </div>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('overtime-requests.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-clock class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.overtime_requests') }}</span>
                                        </div>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('user-schedule-request.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-calendar-clock class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.schedule_request') }}</span>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('daily-travels.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-route class="h-6 w-6 text-primary" />
                                            <span class="ml-2">{{ __('navbar.daily_travels') }}</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                @elseif ($isHrUser)
                    <li>
                        <a href="{{ route('home') }}">
                            <div class="flex items-center">
                                <x-lucide-home class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.home') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('standard.profile.edit') }}">
                            <div class="flex items-center">
                                <x-lucide-circle-user class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.profile') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('attendances.index') }}">

                            <div class="flex items-center">
                                <x-lucide-calendar class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.attendances') }}</span>
                            </div>
                        </a>
                    </li>
                    @if ($canAccessBusinessTrips)
                        <li>
                            <a href="{{ route('business-trips.index') }}">

                                <div class="flex items-center">
                                    <x-lucide-car class="h-6 w-6 text-primary" />
                                    <span class="ml-2">{{ __('navbar.business_trips') }}</span>
                                </div>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('time-off-requests.index') }}">
                            <div class="flex items-center">
                                <x-lucide-sun class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.time_off') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('overtime-requests.index') }}">
                            <div class="flex items-center">
                                <x-lucide-clock class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.overtime_requests') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user-schedule-request.index') }}">
                            <div class="flex items-center">
                                <x-lucide-calendar-clock class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.schedule_request') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('daily-travels.index') }}">
                            <div class="flex items-center">
                                <x-lucide-route class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.daily_travels') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <details>
                            <summary>
                                <x-lucide-contact class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.personnel') }}</span>
                            </summary>
                            <ul>
                                <li>
                                    <a href="{{ route('users.index') }}">
                                        <div class="flex items-center">
                                            <x-lucide-circle-user class="h-4 w-4 text-primary" />
                                            <span class="ml-2">{{ __('navbar.personnel_users') }}</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                @else
                    <li>
                        <a href="{{ route('home') }}">
                            <div class="flex items-center">
                                <x-lucide-home class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.home') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('standard.profile.edit') }}">
                            <div class="flex items-center">
                                <x-lucide-circle-user class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.profile') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('attendances.index') }}">

                            <div class="flex items-center">
                                <x-lucide-calendar class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.attendances') }}</span>
                            </div>
                        </a>
                    </li>
                    @if ($canAccessBusinessTrips)
                        <li>
                            <a href="{{ route('business-trips.index') }}">

                                <div class="flex items-center">
                                    <x-lucide-car class="h-6 w-6 text-primary" />
                                    <span class="ml-2">{{ __('navbar.business_trips') }}</span>
                                </div>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('time-off-requests.index') }}">
                            <div class="flex items-center">
                                <x-lucide-sun class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.time_off') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('overtime-requests.index') }}">
                            <div class="flex items-center">
                                <x-lucide-clock class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.overtime_requests') }}</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('user-schedule-request.index') }}">
                            <div class="flex items-center">
                                <x-lucide-calendar-clock class="h-6 w-6 text-primary" />
                                <span class="ml-2">{{ __('navbar.schedule_request') }}</span>
                            </div>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="fixed bottom-4 right-4 z-50">
        @if (session('success'))
            <div class="alert alert-success shadow-lg cursor-pointer">
                <div class="flex items-center gap-1">
                    <x-lucide-check-circle class="h-6 w-6" />
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error shadow-lg cursor-pointer">
                <div class="flex items-center gap-1">
                    <x-lucide-alert-circle class="h-6 w-6" />
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error shadow-lg cursor-pointer">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="flex items-center gap-1">
                            <x-lucide-alert-circle class="h-6 w-6" />
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @stack('scripts')
</body>
