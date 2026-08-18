<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'membercode')) {
                $table->string('membercode')->nullable()->after('member_number');
                $table->index('membercode');
            }
        });

        Schema::table('saving_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('saving_plans', 'membercode')) {
                $table->string('membercode')->nullable()->after('memberid');
                $table->index('membercode');
            }
        });

        Schema::table('deposits', function (Blueprint $table) {
            if (!Schema::hasColumn('deposits', 'membercode')) {
                $table->string('membercode')->nullable()->after('member_number');
                $table->index('membercode');
            }
        });

        Schema::table('saving_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('saving_balances', 'membercode')) {
                $table->string('membercode')->nullable()->after('customer_id');
                $table->index('membercode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['membercode']);
            $table->dropColumn('membercode');
        });

        Schema::table('saving_plans', function (Blueprint $table) {
            $table->dropIndex(['membercode']);
            $table->dropColumn('membercode');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex(['membercode']);
            $table->dropColumn('membercode');
        });

        Schema::table('saving_balances', function (Blueprint $table) {
            $table->dropIndex(['membercode']);
            $table->dropColumn('membercode');
        });
    }
};
