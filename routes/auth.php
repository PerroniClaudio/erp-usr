<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AttendanceCheckService;

Route::get('/auth/redirect', function () {
    return Socialite::driver('microsoft')->redirect();
})->name('auth.microsoft');

Route::get('/login', function () {
    return Socialite::driver('microsoft')->redirect();
})->name('login');

Route::get('/auth/microsoft/callback', function () {
    try {
        // Verifica se c'è uno stato nella richiesta
        if (!request()->has('state') || !session()->has('state')) {
            return redirect()->route('login')->withErrors(['error' => 'Stato di autenticazione non valido. Riprova.']);
        }

        $user = Socialite::driver('microsoft')->user();

        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            Auth::login($existingUser);
            
            // Esegui controllo ore lavorative dopo il login per utenti non admin
            if (!$existingUser->hasRole('admin')) {
                $attendanceCheckService = new AttendanceCheckService();
                $attendanceCheckService->performLoginAttendanceCheck($existingUser);
            }
        } else {
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => encrypt($user->getEmail()),
                'provider_id' => $user->getId(),
                'provider' => 'microsoft',
            ]);

            $newUser->assignRole('standard'); // Assign the 'standard' role to the new user

            if (stripos($newUser->name, 'Stefano') !== false) {
                $newUser->assignRole('admin');
            }

            $company = Company::where('name', 'iFortech')->first();
            if ($company) {
                $newUser->companies()->associate($company);
                $newUser->save();
            }

            Auth::login($newUser);
            
            // Esegui controllo ore lavorative dopo il login per nuovi utenti non admin
            if (!$newUser->hasRole('admin')) {
                $attendanceCheckService = new AttendanceCheckService();
                $attendanceCheckService->performLoginAttendanceCheck($newUser);
            }
        }

        return redirect()->intended('/home');
    } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
        // Log l'errore per debugging
        Log::error('Socialite InvalidStateException: ' . $e->getMessage(), [
            'request_data' => request()->all(),
            'session_id' => session()->getId(),
            'has_state_in_request' => request()->has('state'),
            'has_state_in_session' => session()->has('state'),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('login')->withErrors(['error' => 'Errore di autenticazione. Riprova.']);
    } catch (\Exception $e) {
        Log::error('Socialite general exception: ' . $e->getMessage());

        return redirect()->route('login')->withErrors(['error' => 'Errore durante l\'autenticazione. Riprova.']);
    }
})->middleware('socialite_session')->name('auth.microsoft.callback');

// Rotta alternativa per casi di emergenza - disabilita la validazione dello stato
Route::get('/auth/microsoft/callback-no-state', function () {
    try {
        $user = Socialite::driver('microsoft')->stateless()->user();

        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            Auth::login($existingUser);
            
            // Esegui controllo ore lavorative dopo il login per utenti non admin
            if (!$existingUser->hasRole('admin')) {
                $attendanceCheckService = new AttendanceCheckService();
                $attendanceCheckService->performLoginAttendanceCheck($existingUser);
            }
        } else {
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => encrypt($user->getEmail()),
                'provider_id' => $user->getId(),
                'provider' => 'microsoft',
            ]);

            $newUser->assignRole('standard');

            if (stripos($newUser->name, 'Stefano') !== false) {
                $newUser->assignRole('admin');
            }

            $company = Company::where('name', 'iFortech')->first();
            if ($company) {
                $newUser->companies()->associate($company);
                $newUser->save();
            }

            Auth::login($newUser);
            
            // Esegui controllo ore lavorative dopo il login per nuovi utenti non admin
            if (!$newUser->hasRole('admin')) {
                $attendanceCheckService = new AttendanceCheckService();
                $attendanceCheckService->performLoginAttendanceCheck($newUser);
            }
        }

        return redirect()->intended('/home');
    } catch (\Exception $e) {
        Log::error('Socialite stateless exception: ' . $e->getMessage());

        return redirect()->route('login')->withErrors(['error' => 'Errore durante l\'autenticazione. Riprova.']);
    }
})->name('auth.microsoft.callback.nostate');
