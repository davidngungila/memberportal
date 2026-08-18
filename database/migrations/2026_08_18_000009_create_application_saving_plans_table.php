<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_saving_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('membership_applications')->onDelete('cascade');
            $table->string('plan_name');
            $table->string('frequency'); // monthly, weekly, quarterly
            $table->decimal('target_amount', 15, 2);
            $table->decimal('periodic_amount', 15, 2)->nullable();
            $table->date('expected_saving_date')->nullable();
            $table->timestamps();

            $table->unique('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_saving_plans');
    }
};
