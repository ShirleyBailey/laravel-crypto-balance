<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BalanceService;
use Exception;
use Illuminate\Support\Facades\DB;
class BalanceController extends Controller
{
    protected $service;

    public function __construct(BalanceService $service)
    {
        $this->service = $service;
    }

    public function deposit(DepositRequest $request)
    {
        $data = $request->validated();

        try {
            $this->service->deposit($data['user_id'], $data['amount'], $data['note'] ?? '');
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
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
            'user_id' => 'required|integer|exists:users,id',
            'amount'  => 'required|numeric|min:0.01',
            'note'    => 'nullable|string|max:255',
        ]);

        try {
            $this->service->withdraw($request->user_id, $request->amount, $request->note ?? '');
            // <- HERE change
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function history($userId)
    {
        return $this->service->history($userId);
    }

    public function balance($userId)
    {
        $balance = DB::table('user_balances')->where('user_id', $userId)->first();
        return response()->json([
            'user_id' => $userId,
            'balance' => $balance?->balance ?? 0
        ]);
    }

    public function transactions($userId)
    {
        $transactions = DB::table('transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions, 200);
    }
}