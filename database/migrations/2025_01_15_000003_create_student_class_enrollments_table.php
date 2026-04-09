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
        Schema::create('student_class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_class_id');
            $table->date('enrollment_date');
            $table->date('withdrawal_date')->nullable();
            $table->enum('status', ['active', 'transferred', 'withdrawn', 'promoted', 'repeated'])->default('active');
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->text('enrollment_notes')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'school_class_id', 'academic_year'], 'stu_class_enroll_unique');
            $table->index(['school_class_id', 'status']);
            $table->index(['academic_year', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_enrollments');
    }
};
