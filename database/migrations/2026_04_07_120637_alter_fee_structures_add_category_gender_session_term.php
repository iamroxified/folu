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
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->enum('category', ['NI', 'OS'])->after('fee_type'); // New Intake or Old Student
            $table->enum('gender', ['M', 'F', 'All'])->default('All')->after('category');
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade')->after('gender');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade')->after('session_id'); // Assuming terms table exists
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('cascade')->after('term_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['term_id']);
            $table->dropForeign(['class_id']);
            $table->dropColumn(['category', 'gender', 'session_id', 'term_id', 'class_id']);
        });
    }
};
