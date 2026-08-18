<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('email_code', 6)->nullable();
            $table->string('phone_code', 6)->nullable();
            $table->timestamp('email_expires_at')->nullable();
            $table->timestamp('phone_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->integer('email_attempts')->default(0);
            $table->integer('phone_attempts')->default(0);
            $table->enum('status', ['pending', 'verified', 'expired'])->default('pending');
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->index(['phone', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
