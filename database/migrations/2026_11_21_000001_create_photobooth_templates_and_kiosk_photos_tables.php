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
        Schema::create('photobooth_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->string('name')->default('Matchday Collectible Template');
            $table->boolean('is_active')->default(true);
            $table->string('frame_overlay')->nullable()->comment('Custom transparent PNG overlay border');
            $table->string('background_image')->nullable()->comment('Custom backdrop if fan photo is cutout');
            $table->string('primary_color', 20)->default('#5A2D82')->comment('Club theme primary hex color (e.g. #5A2D82 Persita purple)');
            $table->string('secondary_color', 20)->default('#F95700')->comment('Club theme secondary / accent hex color');
            $table->string('header_title')->default('I LEAGUE 2026/2027')->nullable();
            $table->string('header_subtitle')->default('FOOTBALL UNITES US')->nullable();
            $table->string('badge_text')->default('MATCHDAY COLLECTIBLE')->nullable();
            $table->string('footer_text')->default('SATU LIGA, SATU SEMANGAT')->nullable();
            $table->string('custom_watermark')->nullable();
            $table->boolean('show_qr')->default(true);
            $table->boolean('show_fan_name')->default(true);
            $table->boolean('show_match_info')->default(true);
            $table->boolean('show_team_logos')->default(true);
            $table->json('layout_config')->nullable()->comment('Custom layout JSON coordinates and sizing');
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->index('event_id');
        });

        Schema::create('kiosk_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30)->nullable();
            $table->string('original_photo')->nullable();
            $table->string('card_photo');
            $table->string('token', 64)->unique();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index('event_id');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kiosk_photos');
        Schema::dropIfExists('photobooth_templates');
    }
};
