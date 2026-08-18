<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('membership_applications')->onDelete('cascade');
            $table->string('membercode')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // mobile_money, bank_transfer, cash, card
            $table->string('transaction_reference')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->enum('status', [
                'pending',
                'processing',
                'successful',
                'failed',
                'cancelled',
                'refunded',
            ])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('status');
            $table->index('transaction_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};
