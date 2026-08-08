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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('member_number')->nullable();
            $table->string('loan_number')->unique();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 15, 2)->default(0);
            $table->integer('term_months');
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->decimal('monthly_payment', 15, 2)->nullable();
            $table->decimal('total_amount_due', 15, 2)->nullable();
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'disbursed', 'active', 'paid', 'defaulted', 'rejected'])->default('pending');
            $table->enum('purpose', ['business', 'education', 'agriculture', 'personal', 'emergency', 'other'])->default('personal');
            $table->text('purpose_description')->nullable();
            $table->text('collateral')->nullable();
            $table->text('guarantor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
