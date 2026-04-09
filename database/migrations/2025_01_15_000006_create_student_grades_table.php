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
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('school_class_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('term', ['first', 'second', 'third', 'final'])->default('first');
            $table->enum('assessment_type', ['assignment', 'quiz', 'test', 'exam', 'project', 'presentation', 'practical'])->default('exam');
            $table->string('assessment_name'); // e.g., "Mid-term Exam", "Final Project"
            $table->decimal('score', 5, 2); // Score obtained
            $table->decimal('max_score', 5, 2); // Maximum possible score
            $table->decimal('percentage', 5, 2)->nullable(); // Calculated percentage
            $table->string('letter_grade', 2)->nullable(); // A+, A, B+, etc.
            $table->decimal('grade_point', 3, 2)->nullable(); // GPA equivalent
            $table->text('comments')->nullable(); // Teacher's comments
            $table->date('assessment_date');
            $table->enum('status', ['draft', 'published', 'revised'])->default('draft');
            $table->boolean('is_final')->default(false); // Is this the final grade for the term
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'term']);
            $table->index(['subject_id', 'academic_year', 'term']);
            $table->index(['school_class_id', 'assessment_type']);
            $table->index(['assessment_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
