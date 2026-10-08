<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'staff_type')) {
                $table->enum('staff_type', ['full_time', 'part_time'])->default('full_time')->after('position');
            }
            if (!Schema::hasColumn('staff', 'assigned_class_id')) {
                $table->foreignId('assigned_class_id')->nullable()->after('staff_type')->constrained('school_classes')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff', 'assigned_subject_id')) {
                $table->foreignId('assigned_subject_id')->nullable()->after('assigned_class_id')->constrained('subjects')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff', 'class_or_subject_custom')) {
                $table->string('class_or_subject_custom')->nullable()->after('assigned_subject_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['assigned_class_id']);
            $table->dropForeign(['assigned_subject_id']);
            $table->dropColumn(['staff_type', 'assigned_class_id', 'assigned_subject_id', 'class_or_subject_custom']);
        });
    }
};
