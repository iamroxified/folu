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
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->string('term'); // e.g., Semester 1, Term 2
            $table->year('academic_year');
            $table->string('subject');
            $table->decimal('grade', 5, 2)->nullable();
            $table->enum('grade_scale', ['4.0', '5.0', '100'])->default('100');
            $table->text('comments')->nullable();
            $table->string('teacher_name')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'withdrawn'])->default('in_progress');
            $table->date('record_date');
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_records');
    }
};
