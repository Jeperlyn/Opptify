<?php

use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\Api\AdminSessionController;
use App\Http\Controllers\Api\EmailSubscriptionController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PesoProgramController;
use App\Http\Controllers\Api\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/programs', [PesoProgramController::class, 'index']);
Route::get('/programs/{pesoProgram}', [PesoProgramController::class, 'show']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::post('/email-subscriptions', [EmailSubscriptionController::class, 'store']);
Route::get('/email-subscriptions', [EmailSubscriptionController::class, 'index']);
Route::post('/admin/session', [AdminSessionController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/me/preferences', [UserPreferenceController::class, 'show']);
    Route::put('/users/me/preferences', [UserPreferenceController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/admin/events', [AdminEventController::class, 'store']);
});
