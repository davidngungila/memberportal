<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'membercode')) {
                $table->string('membercode')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('members', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('membercode')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('members', 'membership_type_id')) {
                $table->foreignId('membership_type_id')->nullable()->after('user_id')->constrained('member_types')->nullOnDelete();
            }
            if (!Schema::hasColumn('members', 'first_name')) {
                $table->string('first_name')->nullable()->after('membership_type_id');
            }
            if (!Schema::hasColumn('members', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('members', 'last_name')) {
                $table->string('last_name')->nullable()->after('middle_name');
            }
            if (!Schema::hasColumn('members', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('members', 'registration_status')) {
                $table->enum('registration_status', [
                    'registered',
                    'pending_documentation',
                    'suspended',
                    'expelled',
                ])->default('registered')->after('status');
            }
            if (!Schema::hasColumn('members', 'joined_at')) {
                $table->timestamp('joined_at')->nullable()->after('registration_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['membership_type_id']);
            $table->dropColumn([
                'membercode',
                'user_id',
                'membership_type_id',
                'first_name',
                'middle_name',
                'last_name',
                'profile_photo',
                'registration_status',
                'joined_at',
            ]);
        });
    }
};
