<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultTypeId = DB::table('member_types')->where('status', 'active')->orderBy('priority', 'desc')->value('id');

        if ($defaultTypeId) {
            DB::table('members')
                ->whereNull('membership_type_id')
                ->update(['membership_type_id' => $defaultTypeId]);
        }
    }

    public function down(): void
    {
        DB::table('members')->update(['membership_type_id' => null]);
    }
};
