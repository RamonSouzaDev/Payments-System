<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ThankYouController;

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
    return redirect()->route('payments.index');
});

// Rotas de pagamento
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('/payments/process', [PaymentController::class, 'process'])->name('payments.process');
Route::post('/payments/boleto', [PaymentController::class, 'processBoleto'])->name('payments.process.boleto');
Route::post('/payments/credit-card', [PaymentController::class, 'processCreditCard'])->name('payments.process.credit-card');
Route::post('/payments/pix', [PaymentController::class, 'processPix'])->name('payments.process.pix');
Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');

// Webhook do Asaas
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);


// Página de agradecimento
Route::get('/thank-you', [ThankYouController::class, 'index'])->name('thank-you');