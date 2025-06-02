<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule';
    protected $fillable = [
        'owner_id',
        'day',
        'start_time',
        'end_time',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
}
