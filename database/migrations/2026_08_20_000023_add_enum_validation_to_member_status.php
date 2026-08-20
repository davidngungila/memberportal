<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE `members` SET `status` = LOWER(`status`) WHERE `status` IS NOT NULL");
        DB::statement("UPDATE `members` SET `status` = 'active' WHERE LOWER(`status`) IN ('provisional', 'registered', 'approved')");
        DB::statement("UPDATE `members` SET `status` = 'pending' WHERE LOWER(`status`) IN ('new', 'awaiting')");
        DB::statement("UPDATE `members` SET `status` = 'inactive' WHERE LOWER(`status`) IN ('dormant', 'disabled')");
        DB::statement("UPDATE `members` SET `status` = 'rejected' WHERE LOWER(`status`) IN ('deny', 'denied')");
        DB::statement("UPDATE `members` SET `status` = 'active' WHERE `status` NOT IN ('pending','active','inactive','suspended','expired','rejected','cancelled')");
        DB::statement("ALTER TABLE `members` MODIFY COLUMN `status` ENUM('pending','active','inactive','suspended','expired','rejected','cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `members` MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'active'");
    }
};
