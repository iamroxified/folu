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
        if (!Schema::hasTable('additional_charges')) {
            Schema::create('additional_charges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->foreignId('student_fee_id')->nullable()->constrained('student_fees')->onDelete('set null');
                $table->foreignId('session_id')->nullable()->constrained('academic_sessions')->onDelete('set null');
                $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('set null');
                $table->string('fee_name');
                $table->decimal('amount', 10, 2);
                $table->text('reason')->nullable();
                $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
                $table->string('status', 20)->default('unpaid'); // unpaid, partially_paid, paid
                $table->timestamps();

                $table->index(['student_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_charges');
    }
};
