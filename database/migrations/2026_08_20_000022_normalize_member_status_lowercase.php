<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')
            ->where('status', 'Active')
            ->update(['status' => 'active']);

        DB::table('members')
            ->where('status', 'Pending')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        DB::table('members')
            ->where('status', 'active')
            ->update(['status' => 'Active']);

        DB::table('members')
            ->where('status', 'pending')
            ->update(['status' => 'Pending']);
    }
};
