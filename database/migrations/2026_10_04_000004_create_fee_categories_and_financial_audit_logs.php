<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fee_categories')) {
            Schema::create('fee_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed fee categories
            $categories = [
                ['name' => 'Tuition', 'code' => 'TUITION'],
                ['name' => 'Registration', 'code' => 'REGISTRATION'],
                ['name' => 'Books', 'code' => 'BOOKS'],
                ['name' => 'Uniform', 'code' => 'UNIFORM'],
                ['name' => 'Examination', 'code' => 'EXAMINATION'],
                ['name' => 'ICT', 'code' => 'ICT'],
                ['name' => 'Development Levy', 'code' => 'DEVELOPMENT_LEVY'],
                ['name' => 'Medical', 'code' => 'MEDICAL'],
                ['name' => 'Transportation', 'code' => 'TRANSPORTATION'],
                ['name' => 'Feeding', 'code' => 'FEEDING'],
                ['name' => 'Extra-curricular', 'code' => 'EXTRACURRICULAR'],
                ['name' => 'Other', 'code' => 'OTHER'],
            ];

            foreach ($categories as $cat) {
                DB::table('fee_categories')->insertOrIgnore([
                    'name' => $cat['name'],
                    'code' => $cat['code'],
                    'description' => $cat['name'] . ' fee category',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (!Schema::hasTable('financial_audit_logs')) {
            Schema::create('financial_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('action'); // e.g., create_fee_structure, edit_fee_structure, add_extra_fee, record_payment
                $table->string('entity_type')->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_audit_logs');
        Schema::dropIfExists('fee_categories');
    }
};
