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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['Admin', 'User', 'Business'])->default('User');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('sub_id');
            $table->string('last_name')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('address')->nullable();
            $table->unsignedDouble('latitude')->nullable();
            $table->unsignedDouble('longitude')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('description')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('email_otp')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->tinyinteger('is_profile_completed')->default(0);
            $table->tinyinteger('is_push_notify')->default(1);
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
            $table->string('social_type')->nullable();
            $table->string('social_token')->nullable();
            $table->tinyinteger('is_forgot')->default(0);
            $table->tinyinteger('is_verfied')->default(0);
            $table->tinyinteger('is_blocked')->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
