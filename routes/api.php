<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BalanceController;

Route::post('/deposit', [BalanceController::class, 'deposit']);
Route::post('/deposit/confirm', [BalanceController::class, 'confirmDeposit']);
Route::post('/withdraw', [BalanceController::class, 'withdraw']);
Route::get('/transactions/{userId}', [BalanceController::class, 'history']);
