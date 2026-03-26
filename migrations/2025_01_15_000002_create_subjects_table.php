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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code')->unique(); // e.g., "MATH101", "ENG201"
            $table->string('subject_name'); // e.g., "Mathematics", "English Literature"
            $table->text('description')->nullable();
            $table->string('department')->nullable(); // e.g., "Science", "Arts", "Commerce"
            $table->integer('credit_hours')->default(1);
            $table->enum('subject_type', ['core', 'elective', 'optional'])->default('core');
            $table->json('grade_levels')->nullable(); // Array of applicable grade levels
            $table->boolean('is_practical')->default(false); // Has practical/lab components
            $table->string('prerequisite_subjects')->nullable(); // Comma-separated subject codes
            $table->enum('status', ['active', 'inactive', 'retired'])->default('active');
            $table->json('assessment_structure')->nullable(); // Stores exam weightage, assignments etc.
            $table->timestamps();

            $table->index(['department', 'subject_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
