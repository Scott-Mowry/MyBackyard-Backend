<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'sender_id', 'receiver_id', 'title', 'description', 'record_id', 'type', 'seen', 'read_at'
    ];

    public function sendBy()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }


}
