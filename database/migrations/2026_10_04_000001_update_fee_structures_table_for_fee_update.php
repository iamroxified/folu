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
            // Drop foreign key first to modify column nullability if needed
            $table->dropForeign(['term_id']);
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->change();
            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            
            if (!Schema::hasColumn('fee_structures', 'itemized_components')) {
                $table->json('itemized_components')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structures', 'itemized_components')) {
                $table->dropColumn('itemized_components');
            }
        });
    }
};
