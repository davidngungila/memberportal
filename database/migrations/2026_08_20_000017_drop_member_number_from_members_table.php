<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')
            ->whereNull('membercode')
            ->whereColumn('membercode', '=', 'member_number')
            ->update(['membercode' => DB::raw('member_number')]);

        DB::table('members')
            ->whereNull('membercode')
            ->where('member_number', '!=', null)
            ->update(['membercode' => DB::raw('member_number')]);

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['member_number']);
            $table->dropColumn('member_number');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('member_number')->unique()->after('id');
        });

        DB::table('members')
            ->whereNull('member_number')
            ->whereColumn('member_number', '=', 'membercode')
            ->update(['member_number' => DB::raw('membercode')]);
    }
};
