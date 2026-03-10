<?php

namespace App\Services;

use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class BalanceService {

    public function deposit(int $userId, float $amount, ?string $note = null) {
        return DB::transaction(function() use ($userId, $amount, $note) {
            $balance = UserBalance::firstOrCreate(['user_id' => $userId]);
            $balance->balance += $amount;
            $balance->save();

            $tx = BalanceTransaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'status' => 'confirmed',
                'note' => $note
            ]);

            return $tx;
        });
    }

    public function withdraw(int $userId, float $amount, ?string $note = null) {
        return DB::transaction(function() use ($userId, $amount, $note) {
            $balance = UserBalance::where('user_id', $userId)->lockForUpdate()->firstOrFail();

            if ($balance->balance < $amount) {
                throw new Exception('Insufficient funds');
            }

            $balance->balance -= $amount;
            $balance->save();

            $tx = BalanceTransaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'status' => 'confirmed',
                'note' => $note
            ]);

            return $tx;
        });
    }
}