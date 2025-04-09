<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ERP iFortech</title>

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
            <div class="flex p-2">
                <label for="my-drawer-2" class="btn btn-outline border-base-200 drawer-button lg:hidden">
                    <x-lucide-menu class="h-6 w-6" />
                </label>
            </div>
            <main class="flex flex-col gap-4">
                {{ $slot }}
            </main>
        </div>
        <div class="drawer-side">
            <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
            <ul class="menu bg-base-300 text-base-content min-h-full w-80 p-4">
                <!-- Sidebar content here -->
                <li>
                    <a href="">
                        <div class="flex items-center">
                            <x-lucide-home class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.home') }}</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="">
                        <div class="flex items-center">
                            <x-lucide-calendar class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.attendances') }}</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="">
                        <div class="flex items-center">
                            <x-lucide-car class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.business_trips') }}</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a href="">
                        <div class="flex items-center">
                            <x-lucide-wallet class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.expenses') }}</span>
                        </div>
                    </a>
                </li>
                <li class="">
                    <a href="">
                        <div class="flex items-center">
                            <x-lucide-sun class="h-6 w-6 text-primary" />
                            <span class="ml-2">{{ __('navbar.time_off') }}</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    @stack('scripts')
</body>
