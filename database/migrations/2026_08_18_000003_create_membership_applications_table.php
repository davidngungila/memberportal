<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('membership_type_id')->nullable()->constrained('member_types')->nullOnDelete();
            $table->string('membercode')->nullable()->unique();
            $table->enum('payment_status', [
                'pending',
                'processing',
                'successful',
                'failed',
                'cancelled',
                'refunded',
            ])->default('pending');
            $table->enum('application_status', [
                'draft',
                'in_progress',
                'submitted',
                'under_review',
                'correction_required',
                'approved',
                'rejected',
            ])->default('draft');
            $table->string('current_stage')->default('account_created');
            $table->text('rejection_reason')->nullable();
            $table->text('correction_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['application_status', 'current_stage']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
