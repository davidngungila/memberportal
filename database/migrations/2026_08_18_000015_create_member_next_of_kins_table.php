<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_next_of_kins', function (Blueprint $table) {
            $table->id();
            $table->string('membercode');
            $table->string('full_name');
            $table->string('relationship');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('membercode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_next_of_kins');
    }
};
