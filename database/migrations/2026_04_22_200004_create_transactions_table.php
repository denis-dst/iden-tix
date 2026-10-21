<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // purchaser
            $table->string('reference_no')->unique(); // TX-YYYYMMDD-XXXX
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('customer_nik')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable(); // gateway reference
            $table->enum('channel', ['online', 'pos'])->default('online');
            $table->foreignId('processed_by')->nullable()->constrained('users'); // for POS transactions
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'payment_status']);
            $table->index('reference_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
