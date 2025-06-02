<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_favorite_words', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_dictionary_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('is_favorite')->default(0)->comment('1 = Yes | 0 = No');
            $table->timestamps();
            $table->foreign('word_dictionary_id')->references('id')->on('words_dictionary')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_words');
    }
};
