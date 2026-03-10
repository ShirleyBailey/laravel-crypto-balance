<?php

use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/deposit', function(Request $request, BalanceService $service) {
    return $service->deposit($request->user_id, $request->amount, $request->note);
});

Route::post('/withdraw', function(Request $request, BalanceService $service) {
    return $service->withdraw($request->user_id, $request->amount, $request->note);
});