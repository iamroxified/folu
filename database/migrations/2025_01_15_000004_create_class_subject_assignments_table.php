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
        Schema::create('class_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('term', ['first', 'second', 'third', 'full_year'])->default('full_year');
            $table->integer('periods_per_week')->default(1);
            $table->time('period_duration')->default('00:40:00'); // 40 minutes default
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id', 'academic_year', 'term'], 'class_sub_assign_unique');
            $table->index(['teacher_id', 'academic_year']);
            $table->index(['academic_year', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject_assignments');
    }
};
