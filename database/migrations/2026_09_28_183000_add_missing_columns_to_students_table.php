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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'user_link')) {
                $table->unsignedBigInteger('user_link')->nullable()->after('id');
            }
            if (!Schema::hasColumn('students', 'student_number')) {
                $table->string('student_number', 100)->nullable()->after('admission_no');
            }
            if (!Schema::hasColumn('students', 'state_of_origin')) {
                $table->string('state_of_origin', 100)->nullable()->after('passport');
            }
            if (!Schema::hasColumn('students', 'lga')) {
                $table->string('lga', 100)->nullable()->after('state_of_origin');
            }
            if (!Schema::hasColumn('students', 'home_address')) {
                $table->text('home_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('students', 'admission_date')) {
                $table->date('admission_date')->nullable()->after('enrollment_date');
            }
            if (!Schema::hasColumn('students', 'student_type')) {
                $table->string('student_type', 50)->default('day')->after('lga');
            }
            if (!Schema::hasColumn('students', 'blood_group')) {
                $table->string('blood_group', 10)->nullable()->after('student_type');
            }
            if (!Schema::hasColumn('students', 'genotype')) {
                $table->string('genotype', 10)->nullable()->after('blood_group');
            }
        });

        if (!Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_link')->nullable();
                $table->string('parent_id', 50)->unique();
                $table->string('first_name', 100);
                $table->string('last_name', 100)->default('');
                $table->string('email', 150)->nullable();
                $table->string('phone', 50);
                $table->string('alternative_phone', 50)->nullable();
                $table->string('occupation', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('relationship_to_student', 50)->default('guardian');
                $table->boolean('emergency_contact')->default(false);
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_parents')) {
            Schema::create('student_parents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_link');
                $table->unsignedBigInteger('parent_link');
                $table->string('relationship', 50)->default('guardian');
                $table->boolean('is_primary_contact')->default(true);
                $table->timestamps();

                $table->index('student_link');
                $table->index('parent_link');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columns = [
                'user_link',
                'student_number',
                'state_of_origin',
                'lga',
                'home_address',
                'admission_date',
                'student_type',
                'blood_group',
                'genotype',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('student_parents');
        Schema::dropIfExists('parents');
    }
};
