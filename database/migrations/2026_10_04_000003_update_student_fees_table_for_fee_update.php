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
        Schema::table('student_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('student_fees', 'session_id')) {
                $table->foreignId('session_id')->nullable()->after('student_id')->constrained('academic_sessions')->onDelete('cascade');
            }
            if (!Schema::hasColumn('student_fees', 'term_id')) {
                $table->foreignId('term_id')->nullable()->after('session_id')->constrained('terms')->onDelete('cascade');
            }
            if (!Schema::hasColumn('student_fees', 'base_amount')) {
                $table->decimal('base_amount', 10, 2)->default(0)->after('amount_due');
            }
            if (!Schema::hasColumn('student_fees', 'additional_amount')) {
                $table->decimal('additional_amount', 10, 2)->default(0)->after('base_amount');
            }
            if (!Schema::hasColumn('student_fees', 'total_payable')) {
                $table->decimal('total_payable', 10, 2)->default(0)->after('additional_amount');
            }
            if (!Schema::hasColumn('student_fees', 'amount_owed')) {
                $table->decimal('amount_owed', 10, 2)->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('student_fees', 'itemized_snapshot')) {
                $table->json('itemized_snapshot')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $columns = ['session_id', 'term_id', 'base_amount', 'additional_amount', 'total_payable', 'amount_owed', 'itemized_snapshot'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('student_fees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
