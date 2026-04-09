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
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('cascade')->after('student_id');
            $table->foreignId('session_id')->nullable()->constrained('academic_sessions')->onDelete('cascade')->after('class_id');
            $table->foreignId('term_id')->nullable()->constrained('terms')->onDelete('cascade')->after('session_id');
            $table->date('date')->after('term_id');
            $table->text('reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['session_id']);
            $table->dropForeign(['term_id']);
            $table->dropColumn(['class_id', 'session_id', 'term_id', 'date', 'reason']);
        });
    }
};
