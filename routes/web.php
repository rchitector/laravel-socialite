<?php

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


Route::get('/auth/github/redirect', function () {
    $url = Socialite::driver('github')->redirect()->getTargetUrl();
    return Inertia::location($url);
})->name('auth.github.redirect');

Route::get('/auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->stateless()->user();
    $user = User::updateOrCreate([
        'provider_id' => $githubUser->getId()
    ], [
        'name' => $githubUser->getName() ?? $githubUser->nickname,
        'email' => $githubUser->getEmail(),
        'provider_type' => 'github',
    ]);
    auth()->login($user);
    return to_route('dashboard');
});

Route::get('/auth/google/redirect', function () {
    $url = Socialite::driver('google')->redirect()->getTargetUrl();
    return Inertia::location($url);
})->name('auth.google.redirect');

Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();
    $user = User::updateOrCreate([
        'provider_id' => $googleUser->getId()
    ], [
        'name' => $googleUser->getName() ?? $googleUser->nickname,
        'email' => $googleUser->getEmail(),
        'provider_type' => 'google',
    ]);
    auth()->login($user);
    return to_route('dashboard');
});
