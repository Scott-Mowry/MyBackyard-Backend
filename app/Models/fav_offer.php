<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fav_offer extends Model
{
    use HasFactory;

    protected $table = 'fav_offers';
    protected $fillable = [
        'offer_id',
        'user_id'
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }
}
