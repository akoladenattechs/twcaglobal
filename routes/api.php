<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DevotionalController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\RadioController;
use App\Http\Controllers\Api\SermonController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes mirror the existing /bli/api/ endpoints so the frontend
| keeps working while we migrate to Laravel.
|
*/

// Public API routes
Route::get('sermons', [SermonController::class, 'index']);
Route::get('sermons/{slug}', [SermonController::class, 'show']);

Route::get('devotionals', [DevotionalController::class, 'index']);
Route::get('devotionals/{slug}', [DevotionalController::class, 'show']);

Route::get('books', [BookController::class, 'index']);
Route::get('books/{slug}', [BookController::class, 'show']);

Route::get('events', [EventController::class, 'index']);
Route::get('events/upcoming', [EventController::class, 'upcoming']);
Route::get('events/{id}', [EventController::class, 'show']);

Route::get('homepage', [HomepageController::class, 'index']);

Route::get('settings', [SettingController::class, 'index']);
Route::get('settings/{key}', [SettingController::class, 'get']);

Route::get('radio', [RadioController::class, 'index']);

Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,60');

Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login')->middleware('throttle:10,60');
Route::post('auth/2fa', [AuthController::class, 'verifyTwoFactor'])->name('api.auth.2fa')->middleware('throttle:10,15');
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth');

// Upload (authenticated)
Route::post('upload', [UploadController::class, 'upload'])->middleware('auth');
