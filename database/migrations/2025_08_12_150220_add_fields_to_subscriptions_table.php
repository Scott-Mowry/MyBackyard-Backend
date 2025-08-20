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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('price');
            $table->enum('billing_cycle', ['monthly', 'annually', 'weekly', 'daily'])->default('monthly')->after('description');
            $table->boolean('is_popular')->default(false)->after('billing_cycle');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active')->after('is_popular');
            $table->boolean('on_show')->default(true)->after('status');
            $table->integer('sort_order')->default(0)->after('on_show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'billing_cycle',
                'is_popular',
                'status',
                'on_show',
                'sort_order'
            ]);
        });
    }
};
