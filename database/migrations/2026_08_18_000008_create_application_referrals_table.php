<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('membership_applications')->onDelete('cascade');
            $table->boolean('was_referred')->default(false);
            $table->string('referee_membercode')->nullable();
            $table->string('referee_name')->nullable();
            $table->timestamps();

            $table->unique('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_referrals');
    }
};
