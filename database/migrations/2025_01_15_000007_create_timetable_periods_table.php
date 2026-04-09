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
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_class_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_number'); // 1st period, 2nd period, etc.
            $table->string('room_number')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('term', ['first', 'second', 'third', 'full_year'])->default('full_year');
            $table->enum('period_type', ['regular', 'practical', 'library', 'sports', 'assembly', 'break'])->default('regular');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('status', ['active', 'cancelled', 'rescheduled', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'day_of_week', 'period_number', 'academic_year', 'term'], 'timetable_unique');
            $table->index(['teacher_id', 'day_of_week', 'academic_year']);
            $table->index(['academic_year', 'status']);
            $table->index(['day_of_week', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_periods');
    }
};
