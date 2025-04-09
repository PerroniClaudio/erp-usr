<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

Route::get('/auth/redirect', function () {
    return Socialite::driver('microsoft')->redirect();
})->name('auth.microsoft');

Route::get('/login', function () {
    return Socialite::driver('microsoft')->redirect();
})->name('login');

Route::get('/auth/microsoft/callback', function () {
    $user = Socialite::driver('microsoft')->user();

    $existingUser = User::where('email', $user->getEmail())->first();

    if ($existingUser) {
        Auth::login($existingUser);
    } else {
        $newUser = User::create([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => encrypt($user->getEmail()),
            'provider_id' => $user->getId(),
            'provider' => 'microsoft',
        ]);

        Auth::login($newUser);
    }

    return redirect()->intended('/home');
});
