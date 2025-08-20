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
        Schema::table('receipts', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('cancelled');
            $table->string('recurring_subscription_id')->nullable()->after('is_recurring');
            $table->string('authorize_transaction_id')->nullable()->after('recurring_subscription_id');
            $table->enum('payment_type', ['one_time', 'recurring', 'promo'])->default('one_time')->after('authorize_transaction_id');
            $table->integer('billing_cycle_number')->nullable()->after('payment_type');
            $table->timestamp('next_billing_date')->nullable()->after('billing_cycle_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn([
                'is_recurring',
                'recurring_subscription_id',
                'authorize_transaction_id',
                'payment_type',
                'billing_cycle_number',
                'next_billing_date'
            ]);
        });
    }
};
