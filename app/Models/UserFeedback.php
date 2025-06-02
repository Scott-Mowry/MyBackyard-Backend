<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeedback extends Model
{
    use HasFactory;
    protected $table = 'user_feedback';
    protected $fillable = [
        'user_id','rate','feedback_text','status'
    ];

    /**
     * get modified by Feedback object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
