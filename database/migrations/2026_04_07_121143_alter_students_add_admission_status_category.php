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
        Schema::table('students', function (Blueprint $table) {
            $table->enum('admission_status', ['pending', 'admitted', 'withdrawn'])->default('pending')->after('status');
            $table->enum('category', ['NI', 'OS'])->default('NI')->after('admission_status'); // New Intake or Old Student
            $table->string('passport')->nullable()->after('category'); // Path to passport photo
            $table->foreignId('current_class_id')->nullable()->constrained('school_classes')->onDelete('set null')->after('passport');
            $table->foreignId('current_session_id')->nullable()->constrained('academic_sessions')->onDelete('set null')->after('current_class_id');
            $table->foreignId('current_term_id')->nullable()->constrained('terms')->onDelete('set null')->after('current_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['current_class_id']);
            $table->dropForeign(['current_session_id']);
            $table->dropForeign(['current_term_id']);
            $table->dropColumn(['admission_status', 'category', 'passport', 'current_class_id', 'current_session_id', 'current_term_id']);
        });
    }
};
