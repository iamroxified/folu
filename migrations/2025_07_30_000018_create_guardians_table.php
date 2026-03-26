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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone');
            $table->string('phone_alt')->nullable();
            $table->text('address');
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->string('work_phone')->nullable();
            $table->text('work_address')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('identification_type')->nullable(); // passport, national_id, license
            $table->string('identification_number')->nullable();
            $table->timestamps();

            $table->index(['email']);
            $table->index(['phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
