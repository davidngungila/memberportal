<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('sms');
            $table->string('name')->nullable();
            $table->string('sms_api_key')->nullable();
            $table->string('messaging_sender_id')->nullable()->default('FEEDTAN');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_profiles');
    }
};
