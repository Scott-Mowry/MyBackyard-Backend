<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFavoriteWords extends Model
{
    use HasFactory;

    protected $table = 'user_favorite_words';
    protected $fillable = [
        'word_dictionary_id','user_id','is_favorite'
    ];

    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function wordsDictionary()
    {
        return $this->belongsTo(WordDictionary::class, 'word_dictionary_id');
    }
}
