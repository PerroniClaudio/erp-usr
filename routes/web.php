<?php

use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/favicon', function () {
    try {
        // Prova prima con S3
        if (Storage::disk('s3')->exists('favicon.ico')) {
            $favicon = Storage::disk('s3')->get('favicon.ico');

            return response($favicon)
                ->header('Content-Type', 'image/x-icon')
                ->header('Cache-Control', 'public, max-age=86400'); // Cache per 24 ore
        }
    } catch (Exception $e) {
        // Log dell'errore per debug
        logger()->warning('S3 favicon access failed: '.$e->getMessage());
    }

    // Fallback alla favicon locale
    $localFavicon = public_path('favicon.ico');
    if (file_exists($localFavicon)) {
        return response()->file($localFavicon);
    }

    // Se non esiste nemmeno la favicon locale, ritorna 404
    return response()->json(['error' => 'Favicon not found'], 404);
})->name('favicon');

Route::get('/home', function () {

    $user = Auth::user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.home');
    }

    return view('home', [
        'failedAttendances' => $user->failedAttendances()->where('status', 0)->get(),
    ]);
})->middleware(['auth'])->name('home');

Route::get('/vehicles/search', function () {
    $query = request('query');
    $vehicles = Vehicle::search($query)->get();
    $vehicles = collect($vehicles->sortByDesc('model'));

    return response()->json(['models' => $vehicles]);
})->middleware(['auth'])->name('vehicles.brands.search');

Route::get('/refresh-csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware(['auth'])->name('csrf.refresh');

require __DIR__.'/auth.php';
require __DIR__.'/standard.php';
require __DIR__.'/admin.php';
require __DIR__.'/test.php';
