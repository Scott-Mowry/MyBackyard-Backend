<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    use HasFactory;

    // If your table name is not the plural of the model name, specify it
    protected $table = 'promocode';

    // Primary key is id (bigint unsigned), so no need to change
    protected $primaryKey = 'id';

    // If the primary key is auto-increment and an integer, leave this as true
    public $incrementing = true;

    // If using bigint, set the key type
    protected $keyType = 'int';

    // Allow mass assignment for these fields
    protected $fillable = [
        'code',
        'sub_duration',
        'claimed_by',
        'subscription_id',
    ];

    // Timestamps are enabled by default, matching created_at and updated_at
    public $timestamps = true;

    /**
     * Relationship: A promo code may belong to a user (claimed_by).
     */
    public function claimedByUser()
    {
        return $this->belongsTo(User::class, 'claimed_by', 'id');
    }

    /**
     * Relationship: The subscription this promo is linked to
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'id');
    }
}
