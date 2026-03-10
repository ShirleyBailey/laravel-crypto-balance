<?php

namespace App\Services;

use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService
{
    // Create a deposit (pending by default)
    public function deposit(int $userId, float $amount, ?string $note = null): BalanceTransaction
    {
        return DB::transaction(function() use ($userId, $amount, $note) {
            $balance = UserBalance::firstOrCreate(['user_id' => $userId]);

            // Create a pending transaction
            $tx = BalanceTransaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'status' => 'pending',
                'note' => $note
            ]);

            return $tx;
        });
    }

    // Confirm a pending deposit
    public function confirmDeposit(int $transactionId): BalanceTransaction
    {
        return DB::transaction(function() use ($transactionId) {
            $tx = BalanceTransaction::lockForUpdate()->findOrFail($transactionId);

            if ($tx->status !== 'pending' || $tx->type !== 'deposit') {
                throw new Exception('Transaction cannot be confirmed.');
            }

            $balance = UserBalance::firstOrCreate(['user_id' => $tx->user_id]);
            $balance->balance += $tx->amount;
            $balance->save();

            $tx->status = 'confirmed';
            $tx->save();

            return $tx;
        });
    }

    // Withdraw with optional fee
    public function withdraw(int $userId, float $amount, float $fee = 0, ?string $note = null): BalanceTransaction
    {
        return DB::transaction(function() use ($userId, $amount, $fee, $note) {
            $balance = UserBalance::where('user_id', $userId)->lockForUpdate()->firstOrFail();
            $total = $amount + $fee;

            if ($balance->balance < $total) {
                throw new Exception('Insufficient funds');
            }

            $balance->balance -= $total;
            $balance->save();

            // Withdraw transaction
            $tx = BalanceTransaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'status' => 'confirmed',
                'note' => $note
            ]);

            // Fee transaction
            if ($fee > 0) {
                BalanceTransaction::create([
                    'user_id' => $userId,
                    'type' => 'fee',
                    'amount' => $fee,
                    'status' => 'confirmed',
                    'note' => 'Withdrawal fee'
                ]);
            }

            return $tx;
        });
    }

    // Get transaction history
    public function history(int $userId)
    {
        return BalanceTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}