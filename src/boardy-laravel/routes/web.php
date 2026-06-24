<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('events.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('events.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::middleware('auth')->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');

    Route::get('/events/{event}/edit', [EventController::class, 'edit'])
        ->whereNumber('event')
        ->name('events.edit');

    Route::put('/events/{event}', [EventController::class, 'update'])
        ->whereNumber('event')
        ->name('events.update');

    Route::patch('/events/{event}', [EventController::class, 'update'])
        ->whereNumber('event');

    Route::delete('/events/{event}', [EventController::class, 'destroy'])
        ->whereNumber('event')
        ->name('events.destroy');

    Route::post('/events/{event}/join', [EventRegistrationController::class, 'store'])
        ->whereNumber('event')
        ->name('events.join');

    Route::delete('/events/{event}/join', [EventRegistrationController::class, 'destroy'])
        ->whereNumber('event')
        ->name('events.leave');
});

Route::get('/events/{event}', [EventController::class, 'show'])
    ->whereNumber('event')
    ->name('events.show');

Route::get('/auth/github', [GitHubController::class, 'redirect'])
    ->middleware('guest')
    ->name('auth.github');

Route::get('/auth/github/callback', [GitHubController::class, 'callback'])
    ->middleware('guest')
    ->name('auth.github.callback');

Route::get('/oauth/callback', function () {
    return view('auth.callback');
})->name('oauth.callback');

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

require __DIR__ . '/auth.php';
