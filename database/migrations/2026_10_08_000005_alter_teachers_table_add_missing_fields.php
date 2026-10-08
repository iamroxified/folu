<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'qualification')) {
                $table->string('qualification')->nullable()->after('position');
            }
            if (!Schema::hasColumn('staff', 'specialization')) {
                $table->text('specialization')->nullable()->after('qualification');
            }
        });

        DB::statement("CREATE OR REPLACE VIEW teachers AS 
            SELECT 
                id, 
                staff_number AS teacher_id, 
                staff_number AS employee_id, 
                first_name, 
                last_name, 
                email, 
                phone, 
                position, 
                qualification, 
                specialization, 
                hire_date AS employment_date, 
                hire_date, 
                status, 
                id AS user_link, 
                created_at, 
                updated_at 
            FROM staff");
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['qualification', 'specialization']);
        });

        DB::statement("CREATE OR REPLACE VIEW teachers AS SELECT id, staff_number AS teacher_id, first_name, last_name, email, phone, status, id AS user_link, created_at, updated_at FROM staff");
    }
};
