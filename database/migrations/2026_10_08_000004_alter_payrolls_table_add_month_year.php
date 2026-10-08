<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'month')) {
                $table->string('month', 20)->nullable()->after('class_subject');
            }
            if (!Schema::hasColumn('payrolls', 'year')) {
                $table->integer('year')->nullable()->after('month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['month', 'year']);
        });
    }
};
