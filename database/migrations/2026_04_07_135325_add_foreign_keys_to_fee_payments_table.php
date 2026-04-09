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
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('staff')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['student_fee_assignment_id']);
            $table->dropForeign(['received_by']);
            $table->dropForeign(['verified_by']);
        });
    }
};
