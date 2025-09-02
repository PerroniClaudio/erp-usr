<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Presenze iFortech</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')
</head>

<body class="antialiased font-sans">
    <main class="flex flex-col items-center justify-center gap-1 h-[100vh]">
        <div class="card card-dash bg-base-200 w-96">
            <div class="card-body">
                <h2 class="card-title">Accedi</h2>
                <p>L'accesso è riservato ai membri del tenant Office 365</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('auth.microsoft') }}"><button class="btn btn-primary">Accedi</button></a>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
