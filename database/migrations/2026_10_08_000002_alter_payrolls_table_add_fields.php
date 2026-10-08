<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'staff_type')) {
                $table->string('staff_type')->default('full_time')->after('staff_id');
            }
            if (!Schema::hasColumn('payrolls', 'class_subject')) {
                $table->string('class_subject')->nullable()->after('staff_type');
            }
            if (!Schema::hasColumn('payrolls', 'payment_method')) {
                $table->string('payment_method')->default('bank_transfer')->after('status');
            }
            if (!Schema::hasColumn('payrolls', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payrolls', 'remarks')) {
                $table->text('remarks')->nullable()->after('payment_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['staff_type', 'class_subject', 'payment_method', 'payment_reference', 'remarks']);
        });
    }
};
