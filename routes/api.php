<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\ProfilePhotoController;
use App\Http\Controllers\Api\V1\RoundController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserProfileController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PreferenceController;
use App\Http\Controllers\Api\V1\PreferenceUserController;
use App\Http\Controllers\Api\V1\PublicUserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::name('v1.')->prefix('v1')->group(function () {
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/login', [AuthController::class, 'login'])->name('login');
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/register', [AuthController::class, 'register'])->name('register');
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/verify', [AuthController::class, 'verify'])->name('verify');
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/resend', [AuthController::class, 'resend'])->name('resend');
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/forgot', [AuthController::class, 'forgot'])->name('forgot');
        Route::middleware(env('APP_ENV') === 'production' ? 'throttle:10,30' : [])->post('/reset', [AuthController::class, 'reset'])->name('reset');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::name('v1.')->prefix('v1')->group(function () {
            Route::name('user.')->group(function () {
                Route::get('user', [UserController::class, 'show'])->name('show');
                Route::patch('user', [UserController::class, 'update'])->name('update');
            });
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::name('user-profile.')->group(function () {
                Route::get('user-profile', [UserProfileController::class, 'show'])->name('show');
                Route::patch('user-profile', [UserProfileController::class, 'update'])->name('update');
            });
            Route::name('profile-photo.')->group(function () {
                Route::get('profile-photo', [ProfilePhotoController::class, 'get'])->name('get');
                Route::put('profile-photo', [ProfilePhotoController::class, 'put'])->name('put');
            });
            Route::name('round.')->group(function () {
                Route::get('round', [RoundController::class, 'index'])->name('index');
                Route::post('round', [RoundController::class, 'store'])->name('store');
                Route::patch('round/{round}', [RoundController::class, 'update'])->name('update');
                Route::delete('round/{round}', [RoundController::class, 'destroy'])->name('destroy');
                Route::get('round/{round}', [RoundController::class, 'show'])->name('show');
                Route::post('round/{round}/join', [RoundController::class, 'join'])->name('join');
                Route::delete('round/{round}/leave', [RoundController::class, 'leave'])->name('leave');
                Route::post('round/{round}/accept', [RoundController::class, 'accept'])->name('accept');
                Route::post('round/{round}/reject', [RoundController::class, 'reject'])->name('reject');
            });
            Route::name('round-user.')->group(function () {
                Route::delete('round-user/{round}', [RoundController::class, 'removeUser'])->name('remove-user');
            });
            Route::name('course.')->group(function () {
                Route::get('course', [CourseController::class, 'index'])->name('index');
            });
            Route::name('notification.')->group(function () {
                Route::get('notification', [NotificationController::class, 'index'])->name('index');
                Route::get('notification/user', [NotificationController::class, 'user'])->name('user');
                Route::patch('notification/{notification}/read', [NotificationController::class, 'read'])->name('read');
                Route::patch('notification/{notification}/unread', [NotificationController::class, 'unread'])->name('unread');
                Route::delete('notification/{notification}', [NotificationController::class, 'destroy'])->name('delete');
            });
            Route::name('preference.')->group(function () {
                Route::get('preference', [PreferenceController::class, 'index'])->name('index');
            });
            Route::name('preference-user.')->group(function () {
                Route::get('preference-user', [PreferenceUserController::class, 'show'])->name('show');
                Route::patch('preference-user', [PreferenceUserController::class, 'update'])->name('update');
            });
            Route::name('public-account.')->group(function () {
                Route::get('public-account/{userId}', [PublicUserProfileController::class, 'show'])->name('show');
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
