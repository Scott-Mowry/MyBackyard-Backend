<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = [
        'category_name','category_icon'
    ];

    /**
     * get modified by Exams object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function categoryWords()
    {
        return $this->hasMany(WordDictionary::class, 'category_id');
    }
}
