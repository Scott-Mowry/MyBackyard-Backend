<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordDictionary extends Model
{
    use HasFactory;

    protected $table = 'words_dictionary';
    protected $fillable = [
        'category_id', 'language', 'word', 'pronunciation', 'description', 'requested_by', 'approved_by', 'is_approved'
    ];


    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function wordCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function userFavWord()
    {
        return $this->hasMany(UserFavoriteWords::class, 'word_dictionary_id');
    }

    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function word_Data()
    {
        return $this->hasMany(WordData::class, 'word_dictionary_id');
    }



    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * get modified by WordDictionary object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Search words from word dictionary
    public function findByWordAndCategory($word, $category_id)
    {
        $query = self::query();

        if (!empty($word)) {
            $query->where('word', 'like', '%' . $word . '%');
        }

        if ($category_id !== null && $category_id !== 0) {
            $query->where('category_id', $category_id);
        }

        // return $query->with('requestedBy')->with('approvedBy')->get();
        return $query->get();
    }
}
