<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change payable_type from enum to varchar to support polymorphic payments including additional_charge
        DB::statement("ALTER TABLE payments MODIFY COLUMN payable_type VARCHAR(191) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payable_type ENUM('student_fee', 'staff_salary') NOT NULL");
    }
};
