<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::middleware(env('APP_ENV') === 'production' ? 'throttle:3,10' : [])->post('/register', [AuthController::class, 'register'])->name('register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::name('v1.')->prefix('v1')->group(function () {
            Route::name('user.')->group(function () {
                Route::get('user', [UserController::class, 'show'])->name('show');
            });
            Route::name('user-profile.')->group(function () {
                Route::get('user-profile', [UserProfileController::class, 'show'])->name('show');
                Route::patch('user-profile', [UserProfileController::class, 'update'])->name('update');
            });
        });
    });
});
Route::middleware('auth:sanctum')->get('/', function (Request $request) {
    return response()->json([
        'data' => 'sample data',
        'message' => 'sample message',
        'status' => 200,
    ], 200);
});
