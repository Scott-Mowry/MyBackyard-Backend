<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->double('top_Left_latitude')->nullable();
            $table->double('top_Left_longitude')->nullable();
            $table->double('bottom_right_latitude')->nullable();
            $table->double('bottom_right_longitude')->nullable();
            $table->tinyinteger('is_allowed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
