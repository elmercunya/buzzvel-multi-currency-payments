<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount_local',
        'currency_code',
        'reason',
        'exchange_rate_eur_to_local',
        'amount_eur',
        'exchange_rate_source',
        'exchange_rate_fetched_at',
        'status',
        'expires_at'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

