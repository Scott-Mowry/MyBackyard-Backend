<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';
    protected $fillable = [
        'title',
        'category_id',
        'owner_id',
        'actual_price',
        'discount_price',
        'reward_points',
        'short_detail',
        'description',
        'image'
    ];

    public function saved_offer()
    {
        return $this->hasMany(saved_offer::class, 'offer_id', 'id');
    }

    public function fav_offer()
    {
        return $this->hasMany(fav_offer::class, 'offer_id', 'id');
    }
}
