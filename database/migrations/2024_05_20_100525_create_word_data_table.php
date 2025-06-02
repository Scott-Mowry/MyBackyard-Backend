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
        Schema::create('word_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_dictionary_id');
            $table->enum('word_data_type',['noun','adjective','pronoun','verb'])->nullable();
            $table->string('word_data_text')->nullable();
            $table->foreign('word_dictionary_id')->references('id')->on('words_dictionary')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('word_data');
    }
};
