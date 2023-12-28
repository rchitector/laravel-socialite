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


Route::get('/auth/{driver}/redirect', function (string $driver) {
    try {
        $url = Socialite::driver($driver)->redirect()->getTargetUrl();
    } catch (Exception $e) {
        return to_route('login')->dangerBanner('Authentication driver "'.$driver.'" not configured');
    }
    return Inertia::location($url);
})->name('auth.driver.redirect');

Route::get('/auth/{driver}/callback', function (string $driver) {
    try {
        $driverUser = Socialite::driver($driver)->stateless()->user();
    } catch (Exception $e) {
        return to_route('login')->dangerBanner('Authentication driver "'.$driver.'" not configured');
    }
    $user = User::updateOrCreate([
        'provider_id' => $driverUser->getId()
    ], [
        'name' => $driverUser->getName() ?? $driverUser->nickname,
        'email' => $driverUser->getEmail(),
        'provider_type' => $driver,
    ]);
    auth()->login($user);
    return to_route('dashboard');
})->name('auth.driver.callback');
