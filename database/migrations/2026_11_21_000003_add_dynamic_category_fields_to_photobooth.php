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
        Schema::table('kiosk_photos', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('transaction_id')->comment('Dynamic ticket category name selected by fan');
        });

        Schema::table('photobooth_templates', function (Blueprint $table) {
            $table->string('category_label_format')->default('{category} • MATCHDAY COLLECTIBLE')->after('badge_text')->comment('Format for dynamic category ribbon display');
            $table->boolean('show_category')->default(true)->after('show_fan_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kiosk_photos', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });

        Schema::table('photobooth_templates', function (Blueprint $table) {
            $table->dropColumn(['category_label_format', 'show_category']);
        });
    }
};
