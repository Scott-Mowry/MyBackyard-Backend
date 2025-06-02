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
        Schema::create('words_dictionary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id'); // Change 'caregory_id' to 'category_id'
            $table->enum('language',['en','ru'])->default('en');
            $table->string('word')->nullable();
            $table->string('pronunciation')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('is_approved')->default(0)->comment('1 = Approved | 0 = Not Approved');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
        });

    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words_dictionary');
    }
};
