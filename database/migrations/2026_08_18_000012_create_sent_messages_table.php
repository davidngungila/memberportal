<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_messages', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('sms');
            $table->string('to');
            $table->string('from')->nullable();
            $table->text('message');
            $table->string('status')->default('pending');
            $table->string('message_id')->nullable();
            $table->json('api_response')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('to');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_messages');
    }
};
