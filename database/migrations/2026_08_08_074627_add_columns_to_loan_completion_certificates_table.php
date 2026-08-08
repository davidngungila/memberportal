<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loan_completion_certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_completion_certificates', 'certificate_number')) {
                $table->string('certificate_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('loan_completion_certificates', 'loan_id')) {
                $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('cascade')->after('certificate_number');
            }
            if (!Schema::hasColumn('loan_completion_certificates', 'completion_date')) {
                $table->date('completion_date')->nullable()->after('loan_id');
            }
            if (!Schema::hasColumn('loan_completion_certificates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('completion_date');
            }
            if (!Schema::hasColumn('loan_completion_certificates', 'notes')) {
                $table->text('notes')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_completion_certificates', function (Blueprint $table) {
            $table->dropForeign(['loan_id']);
            $table->dropColumn(['certificate_number', 'loan_id', 'completion_date', 'is_active', 'notes']);
        });
    }
};
