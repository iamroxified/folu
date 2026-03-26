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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name'); // e.g., "Grade 1A", "Form 2B"
            $table->string('grade_level'); // e.g., "Grade 1", "Form 2"
            $table->string('section')->nullable(); // e.g., "A", "B", "C"
            $table->foreignId('class_teacher_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->integer('max_capacity')->default(30);
            $table->integer('current_enrollment')->default(0);
            $table->string('classroom_location')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['class_name', 'academic_year']);
            $table->index(['grade_level', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
