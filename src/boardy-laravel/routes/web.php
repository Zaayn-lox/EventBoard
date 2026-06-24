<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('events.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('events.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/health', function () {
    return response()->json(['ok' => true]);
})->name('health');

Route::resource('events', EventController::class)
    ->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::resource('events', EventController::class)
        ->except(['index', 'show']);

    Route::post('/events/{event}/join', [EventRegistrationController::class, 'store'])
        ->name('events.join');

    Route::delete('/events/{event}/join', [EventRegistrationController::class, 'destroy'])
        ->name('events.leave');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

Route::get('/auth/github', [GitHubController::class, 'redirect'])
    ->middleware('guest')
    ->name('auth.github');

Route::get('/auth/github/callback', [GitHubController::class, 'callback'])
    ->middleware('guest')
    ->name('auth.github.callback');

require __DIR__.'/auth.php';
