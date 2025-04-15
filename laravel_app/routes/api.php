<?php

use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->prefix('v1')->group(function () {
    Route::post('/payments', [PaymentApiController::class, 'store']);
    Route::get('/payments/{id}', [PaymentApiController::class, 'show']);
    Route::get('/payments/{id}/status', [PaymentApiController::class, 'status']);
});
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
