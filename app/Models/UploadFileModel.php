<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFileModel extends Model
{
    use HasFactory;

    protected $table = 'upload_files';

    protected $fillable = [
        'hash',
        'user_id',
        'name',
        'attachment',
        'size',
        'type',
        'created_at',
        'updated_at'
    ];
}
