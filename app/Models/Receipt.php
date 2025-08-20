<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'payment_date',
        'amount',
        'duration',
        'strikes',
        'cancelled',
        'is_recurring',
        'recurring_subscription_id',
        'authorize_transaction_id',
        'payment_type',
        'billing_cycle_number',
        'next_billing_date'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'cancelled' => 'boolean'
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(subscription::class);
    }
}
