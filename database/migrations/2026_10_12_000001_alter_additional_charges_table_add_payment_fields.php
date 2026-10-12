<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('additional_charges', function (Blueprint $table) {
            if (!Schema::hasColumn('additional_charges', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->default(0.00)->after('amount');
            }
            if (!Schema::hasColumn('additional_charges', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0.00)->after('amount_paid');
            }
        });

        // Initialize balance to amount for all unpaid additional charges
        DB::statement("UPDATE additional_charges SET balance = amount - amount_paid WHERE balance = 0 AND amount > 0 AND status != 'paid'");
    }

    public function down(): void
    {
        Schema::table('additional_charges', function (Blueprint $table) {
            if (Schema::hasColumn('additional_charges', 'balance')) {
                $table->dropColumn('balance');
            }
            if (Schema::hasColumn('additional_charges', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
        });
    }
};
