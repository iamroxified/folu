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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Tuition Fee", "Registration Fee", "Transport Fee"
            $table->string('description')->nullable();
            $table->string('grade_level')->nullable(); // e.g., "Grade 1", "Grade 2", "All"
            $table->string('class_name')->nullable(); // e.g., "Class A", "Class B", "All"
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['one_time', 'monthly', 'quarterly', 'semi_annual', 'annual']);
            $table->enum('fee_type', ['tuition', 'registration', 'transport', 'library', 'sports', 'laboratory', 'uniform', 'exam', 'other']);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('additional_details')->nullable(); // For storing additional fee breakdown
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
