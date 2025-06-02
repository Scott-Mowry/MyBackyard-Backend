<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class places extends Model
{
    use HasFactory;

    protected $table = 'places';
    protected $fillable = [
        'name',
        'top_Left_latitude',
        'top_Left_longitude',
        'bottom_right_latitude',
        'bottom_right_longitude',
        'is_allowed'
    ];
}
