<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('staff_number')->unique();
            $table->string('full_name');
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('residential_address')->nullable();

            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('employment_type')->nullable()->comment('full_time, part_time, contract, intern');
            $table->date('hire_date')->nullable();
            $table->date('end_date')->nullable()->comment('for contract/intern');
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('branch')->nullable();

            $table->string('highest_qualification')->nullable()->comment('e.g. Diploma, Bachelor, Master, PhD');
            $table->string('field_of_study')->nullable();
            $table->string('institution')->nullable();
            $table->year('year_of_graduation')->nullable();
            $table->string('professional_license')->nullable();
            $table->date('license_expiry')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            $table->string('status')->default('active')->comment('active, inactive, suspended, terminated');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
