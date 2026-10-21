<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('venue');
            $table->string('city')->nullable();
            $table->string('background_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->dateTime('event_start_date');
            $table->dateTime('event_end_date');
            $table->dateTime('gate_open_at')->nullable();
            $table->dateTime('gate_close_at')->nullable();
            $table->enum('status', ['draft', 'published', 'private', 'ongoing', 'ended', 'cancelled'])->default('draft');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
