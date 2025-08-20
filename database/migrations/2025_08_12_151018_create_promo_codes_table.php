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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // The actual promo code (e.g., "BUSINESS50")
            $table->string('name'); // Human readable name (e.g., "50% Business Discount")
            $table->text('description')->nullable(); // Description of the promo

            // Discount details
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'free_trial']); // Type of discount
            $table->decimal('discount_value', 8, 2)->nullable(); // Percentage or fixed amount
            $table->integer('free_days')->nullable(); // Number of free days for free_trial type

            // Usage limits
            $table->integer('usage_limit')->nullable(); // Maximum number of uses (null = unlimited)
            $table->integer('usage_count')->default(0); // Current usage count
            $table->integer('per_user_limit')->nullable(); // Max uses per user (null = unlimited)

            // Validity period
            $table->datetime('starts_at')->nullable(); // When promo becomes active
            $table->datetime('expires_at')->nullable(); // When promo expires

            // Targeting
            $table->enum('target_role', ['Business', 'User', 'Both'])->default('Business'); // Who can use it
            $table->json('applicable_subscriptions')->nullable(); // Which subscription IDs this applies to

            // Status and settings
            $table->enum('status', ['active', 'inactive', 'expired', 'draft'])->default('active');
            $table->boolean('is_featured')->default(false); // Show prominently
            $table->integer('sort_order')->default(0); // Display order

            // Admin tracking
            $table->unsignedBigInteger('created_by')->nullable(); // Admin who created it
            $table->text('admin_notes')->nullable(); // Internal notes

            $table->timestamps();

            // Indexes
            $table->index(['code', 'status']);
            $table->index(['target_role', 'status']);
            $table->index(['starts_at', 'expires_at']);
            $table->index('sort_order');

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
