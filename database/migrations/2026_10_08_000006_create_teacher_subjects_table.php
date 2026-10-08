<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_subjects')) {
            Schema::create('teacher_subjects', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('teacher_link');
                $table->unsignedBigInteger('subject_link');
                $table->unsignedBigInteger('class_link')->nullable();
                $table->unsignedBigInteger('academic_session_link')->nullable();
                $table->timestamps();

                $table->index('teacher_link');
                $table->index('subject_link');
                $table->index('class_link');
                $table->index('academic_session_link');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
