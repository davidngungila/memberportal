<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('membership_applications')->onDelete('cascade');
            $table->string('document_type'); // passport_photo, national_id_copy, signature, other
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
