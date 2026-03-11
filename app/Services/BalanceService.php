<?php

namespace App\Services;

use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService
{

    public function deposit(int $userId, float $amount, string $note = ''): void
    {
        if ($amount <= 0) {
            throw new Exception("Deposit amount must be positive");
        }

        DB::transaction(function () use ($userId, $amount, $note) {
            // Lock user balance row for update
            $balance = DB::table('user_balances')->where('user_id', $userId)->lockForUpdate()->first();

            if ($balance) {
                $newBalance = $balance->balance + $amount;
                DB::table('user_balances')->where('user_id', $userId)->update([
                    'balance' => $newBalance,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('user_balances')->insert([
                    'user_id' => $userId,
                    'balance' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Log transaction
            DB::table('transactions')->insert([
                'user_id' => $userId,
                'type'    => 'deposit',
                'amount'  => $amount,
                'note'    => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
    public function withdraw(int $userId, float $amount, float $fee = 0.0, string $note = ''): void
    {
        if ($amount <= 0) {
            throw new Exception("Withdraw amount must be positive");
        }

        DB::transaction(function () use ($userId, $amount, $fee, $note) {
            $balance = DB::table('user_balances')->where('user_id', $userId)->lockForUpdate()->first();

            if (!$balance) {
                throw new Exception("User balance not found");
            }

            $total = $amount + $fee;

            if ($balance->balance < $total) {
                throw new Exception("Insufficient balance");
            }

            $newBalance = $balance->balance - $total;

            DB::table('user_balances')->where('user_id', $userId)->update([
                'balance' => $newBalance,
                'updated_at' => now(),
            ]);

            // Log withdraw transaction
            DB::table('transactions')->insert([
                'user_id' => $userId,
                'type'    => 'withdraw',
                'amount'  => $amount,
                'note'    => $note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log fee as separate transaction (optional)
            if ($fee > 0) {
                DB::table('transactions')->insert([
                    'user_id' => $userId,
                    'type'    => 'fee',
                    'amount'  => $fee,
                    'note'    => 'Fee: ' . $note,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
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