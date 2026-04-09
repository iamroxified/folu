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
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->date('attendance_date');
            $table->enum('attendance_type', ['daily', 'period', 'event'])->default('daily');
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'sick', 'holiday'])->default('present');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('term', ['first', 'second', 'third'])->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date', 'attendance_type', 'subject_id'], 'stu_attend_unique');
            $table->index(['attendance_date', 'status']);
            $table->index(['school_class_id', 'attendance_date']);
            $table->index(['academic_year', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
