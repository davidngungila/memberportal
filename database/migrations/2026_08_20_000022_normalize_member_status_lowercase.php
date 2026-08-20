<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('members')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active']);
    }

    public function down(): void
    {
        // No rollback needed
    }
};
