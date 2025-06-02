<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';
    protected $fillable = [
        'name',
        'type',
        'price',
    ];

    public function sub_points()
    {
        return $this->hasMany(sub_points::class, 'sub_id', 'id');
    }
}
