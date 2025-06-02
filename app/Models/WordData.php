<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordData extends Model
{
    use HasFactory;

    protected $table = 'word_data';
    protected $fillable = [
        'word_dictionary_id','word_data_type','word_data_text'
    ];


    /**
     * get modified by Exams object
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function wordDictionary()
    {
        return $this->belongsTo(WordDictionary::class, 'word_dictionary_id');
    }
}
