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
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // e.g., birth_certificate, transcript, passport
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_size');
            $table->string('file_mime_type');
            $table->text('notes')->nullable();
            $table->date('upload_date');
            $table->timestamps();

            $table->index(['student_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
