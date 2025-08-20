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
        Schema::table('users', function (Blueprint $table) {
            $table->string('recurring_subscription_id')->nullable()->after('sub_id');
            $table->timestamp('recurring_subscription_start_date')->nullable()->after('recurring_subscription_id');
            $table->enum('recurring_subscription_status', ['active', 'cancelled', 'suspended', 'expired'])->nullable()->after('recurring_subscription_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'recurring_subscription_id',
                'recurring_subscription_start_date',
                'recurring_subscription_status'
            ]);
        });
    }
};
