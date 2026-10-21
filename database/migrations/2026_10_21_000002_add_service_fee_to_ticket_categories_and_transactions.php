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
        if (Schema::hasTable('ticket_categories') && !Schema::hasColumn('ticket_categories', 'service_fee')) {
            Schema::table('ticket_categories', function (Blueprint $table) {
                $table->decimal('service_fee', 12, 2)->default(0)->after('price');
            });
        }

        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'service_fee')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->decimal('service_fee', 12, 2)->default(0)->after('discount_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ticket_categories') && Schema::hasColumn('ticket_categories', 'service_fee')) {
            Schema::table('ticket_categories', function (Blueprint $table) {
                $table->dropColumn('service_fee');
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'service_fee')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('service_fee');
            });
        }
    }
};
