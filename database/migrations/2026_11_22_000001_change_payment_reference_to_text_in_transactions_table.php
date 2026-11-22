<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_reference TEXT NULL");
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->text('payment_reference')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_reference VARCHAR(255) NULL");
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('payment_reference', 255)->nullable()->change();
            });
        }
    }
};
