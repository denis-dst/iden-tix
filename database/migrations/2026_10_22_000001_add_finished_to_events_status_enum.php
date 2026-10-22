<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'private', 'ongoing', 'ended', 'cancelled', 'finished') NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE events MODIFY COLUMN status ENUM('draft', 'published', 'private', 'ongoing', 'ended', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
