<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['member_type_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'occupation',
                'employer',
                'address',
                'photo',
                'registration_date',
                'member_type_id',
                'email',
                'phone',
            ]);
        });

        Schema::dropIfExists('member_profiles');
    }

    public function down(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable();
            $table->string('passport_driving_license')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('status')->default('active');
            $table->string('phone_number')->nullable();
            $table->string('alternative_phone')->nullable();
            $table->string('email_address')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street_village')->nullable();
            $table->text('physical_address')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('membership_category')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer_business')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();
            $table->string('introduced_by')->nullable();
            $table->decimal('joining_fee', 12, 2)->nullable();
            $table->decimal('shares_purchased', 12, 2)->nullable();
            $table->decimal('initial_savings_deposit', 12, 2)->nullable();
            $table->string('kin_full_name')->nullable();
            $table->string('kin_relationship')->nullable();
            $table->string('kin_phone_number')->nullable();
            $table->string('kin_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('mobile_money_network')->nullable();
            $table->string('mobile_wallet_number')->nullable();
            $table->string('passport_photo')->nullable();
            $table->string('national_id_copy')->nullable();
            $table->string('signature')->nullable();
            $table->json('other_attachments')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('phone');
            $table->string('occupation')->nullable()->after('gender');
            $table->string('employer')->nullable()->after('occupation');
            $table->text('address')->nullable()->after('employer');
            $table->string('photo')->nullable()->after('branch');
            $table->date('registration_date')->nullable()->after('status');
            $table->foreignId('member_type_id')->nullable()->after('registration_date')->constrained('member_types')->nullOnDelete();
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
        });
    }
};
