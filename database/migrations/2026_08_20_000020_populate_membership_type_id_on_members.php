<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Link members to users by matching membercode
        $users = DB::table('users')->whereNotNull('membercode')->pluck('id', 'membercode');
        $membersWithoutUser = DB::table('members')->whereNull('user_id')->get();

        foreach ($membersWithoutUser as $member) {
            if (isset($users[$member->membercode])) {
                DB::table('members')->where('id', $member->id)->update(['user_id' => $users[$member->membercode]]);
            }
        }

        // Assign default membership type to members missing it
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
