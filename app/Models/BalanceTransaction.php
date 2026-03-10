<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceTransaction extends Model
{
    protected $fillable = ['user_id','type','amount','status','note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserBalance::class, 'user_id');
    }
}