<?php

use App\Events\RoundMessageEvent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/trigger', function () {
    $event = new RoundMessageEvent('hello world at:'.now());
    broadcast($event);
    return 'fired at:'.now();
});

Route::get('/google/redirect',
    [App\Http\Controllers\GoogleLoginController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('/google/callback',
    [App\Http\Controllers\GoogleLoginController::class, 'handleGoogleCallback'])->name('google.callback');
