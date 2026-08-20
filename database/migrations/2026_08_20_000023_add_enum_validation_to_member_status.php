<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `members` MODIFY COLUMN `status` ENUM('pending','active','inactive','suspended','expired','rejected','cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `members` MODIFY COLUMN `status` VARCHAR(50) DEFAULT 'active'");
    }
};
