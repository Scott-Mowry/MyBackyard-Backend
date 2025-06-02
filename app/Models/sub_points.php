<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sub_points extends Model
{
    use HasFactory;

    protected $table = 'sub_points';
    protected $fillable = [
        'point',
        'sub_id'
    ];

    public function subscriptions()
    {
        return $this->belongsTo(subscription::class, 'sub_id', 'id');
    }
}
