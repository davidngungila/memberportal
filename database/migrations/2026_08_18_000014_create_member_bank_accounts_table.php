<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('membercode');
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('branch')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('membercode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_bank_accounts');
    }
};
