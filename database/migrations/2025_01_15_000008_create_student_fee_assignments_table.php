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
        Schema::create('student_fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_structure_id');
            $table->unsignedBigInteger('school_class_id')->nullable();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->enum('term', ['first', 'second', 'third', 'annual'])->nullable();
            $table->decimal('assigned_amount', 10, 2); // May differ from structure amount due to scholarships/discounts
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_reason')->nullable();
            $table->decimal('penalty_amount', 10, 2)->default(0.00);
            $table->string('penalty_reason')->nullable();
            $table->decimal('final_amount', 10, 2); // After discounts and penalties
            $table->date('due_date');
            $table->date('grace_period_end')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'waived', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'status']);
            $table->index(['fee_structure_id', 'academic_year']);
            $table->index(['due_date', 'status']);
            $table->index(['school_class_id', 'academic_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_assignments');
    }
};
