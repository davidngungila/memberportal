<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Link members to users by matching membercode
        $usersByMembercode = DB::table('users')->whereNotNull('membercode')->pluck('id', 'membercode');
        $membersWithoutUser = DB::table('members')->whereNull('user_id')->get();

        foreach ($membersWithoutUser as $member) {
            $userId = $usersByMembercode[$member->membercode] ?? null;

            if (!$userId && $member->full_name) {
                $userId = DB::table('users')->where('name', $member->full_name)->value('id');
            }

            if (!$userId && $member->phone) {
                $userId = DB::table('users')->where('phone', $member->phone)->value('id');
            }

            if (!$userId && $member->email) {
                $userId = DB::table('users')->where('email', $member->email)->value('id');
            }

            if ($userId) {
                DB::table('members')->where('id', $member->id)->update(['user_id' => $userId]);
            }
        }

        // Step 2: Create user records for any remaining members without a user
        $stillUnlinked = DB::table('members')->whereNull('user_id')->get();
        foreach ($stillUnlinked as $member) {
            $userId = DB::table('users')->insertGetId([
                'name' => $member->full_name ?? 'Member ' . $member->membercode,
                'password' => bcrypt('password'),
                'role' => 'member',
                'membercode' => $member->membercode,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('members')->where('id', $member->id)->update(['user_id' => $userId]);
        }

        // Step 3: Assign default membership type to members missing it
        $defaultTypeId = DB::table('member_types')->where('status', 'active')->orderBy('priority', 'desc')->value('id');
        if ($defaultTypeId) {
            DB::table('members')
                ->whereNull('membership_type_id')
                ->update(['membership_type_id' => $defaultTypeId]);
        }
    }

    public function down(): void
    {
        // No rollback — data migration
    }
};
