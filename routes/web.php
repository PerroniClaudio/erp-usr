<?php

use App\Http\Controllers\FileObjectController;
use App\Models\Vehicle;
use App\Services\AttendanceCheckService;
use App\Services\UserWeeklyCalendarService;
use Carbon\Carbon;
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

    // Esegui controllo ore lavorative per utenti standard
    $attendanceCheckService = new AttendanceCheckService();
    $attendanceCheckService->performLoginAttendanceCheck($user);

    $calendarService = app(UserWeeklyCalendarService::class);
    $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
    $weeklyPlan = $calendarService->buildForUser($user, $weekStart);

    return view('home', [
        'failedAttendances' => $user->failedAttendances()->where('status', 0)->get(),
        'weeklyPlan' => $weeklyPlan,
    ]);
})->middleware(['auth'])->name('home');

Route::get('/home/weekly-events', function () {
    $user = Auth::user();
    abort_unless($user, 403);

    $calendarService = app(UserWeeklyCalendarService::class);
    $start = request('start')
        ? Carbon::parse(request('start'))
        : Carbon::now()->startOfWeek(Carbon::MONDAY);
    $end = request('end') ? Carbon::parse(request('end')) : null;

    $events = $calendarService->buildEventsForUser($user, $start, $end);

    return response()->json($events->toArray());
})->middleware(['auth'])->name('home.weekly-events');

Route::get('/vehicles/search', function () {
    $query = request('query');
    $vehicles = Vehicle::search($query)->get();
    $vehicles = collect($vehicles->sortByDesc('model'));

    return response()->json(['models' => $vehicles]);
})->middleware(['auth'])->name('vehicles.brands.search');

Route::get('/refresh-csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware(['auth'])->name('csrf.refresh');

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'files',
], function () {
    Route::get('/{fileObject}/download', [FileObjectController::class, 'download'])->name('files.download');
    Route::delete('/{fileObject}', [FileObjectController::class, 'destroy'])->name('files.destroy');
    Route::get('/{fileObject}/versions', [FileObjectController::class, 'versions'])->name('files.versions');
    Route::post('/{fileObject}/versions', [FileObjectController::class, 'uploadVersion'])->name('files.versions.upload');
});

require __DIR__.'/auth.php';
require __DIR__.'/standard.php';
require __DIR__.'/admin.php';
require __DIR__.'/test.php';
