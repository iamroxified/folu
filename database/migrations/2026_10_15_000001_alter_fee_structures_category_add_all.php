<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE fee_structures MODIFY COLUMN category ENUM('NI', 'OS', 'ALL') NOT NULL DEFAULT 'ALL'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE fee_structures MODIFY COLUMN category ENUM('NI', 'OS') NOT NULL DEFAULT 'OS'");
    }
};

