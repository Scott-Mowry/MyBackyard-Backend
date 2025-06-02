<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class saved_offer extends Model
{
    use HasFactory;

    protected $table = 'saved_offers';
    protected $fillable = [
        'offer_id',
        'user_id',
        'is_claimed'
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }
}
