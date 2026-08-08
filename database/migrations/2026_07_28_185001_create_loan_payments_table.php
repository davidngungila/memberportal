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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->decimal('payment_amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->decimal('principal_amount', 15, 2)->nullable();
            $table->timestamps();
            
            $table->index('loan_id');
            $table->index('user_id');
            $table->index('customer_id');
            $table->index('payment_date');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
