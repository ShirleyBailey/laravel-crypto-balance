<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceService;
use Exception;

class BalanceController extends Controller
{
    protected $service;

    public function __construct(BalanceService $service)
    {
        $this->service = $service;
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string'
        ]);

        return $this->service->deposit($request->user_id, $request->amount, $request->note);
    }

    public function confirmDeposit(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|integer'
        ]);

        try {
            return $this->service->confirmDeposit($request->transaction_id);
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 400);
        }
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'note' => 'nullable|string'
        ]);

        try {
            return $this->service->withdraw(
                $request->user_id,
                $request->amount,
                $request->fee ?? 0,
                $request->note
            );
        } catch (Exception $e) {
            return response(['error' => $e->getMessage()], 400);
        }
    }

    public function history($userId)
    {
        return $this->service->history($userId);
    }
}