<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_next_of_kin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('membership_applications')->onDelete('cascade');
            $table->string('full_name');
            $table->string('relationship');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_next_of_kin');
    }
};
