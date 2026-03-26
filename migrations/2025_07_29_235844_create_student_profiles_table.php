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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->default('single');
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->text('previous_education')->nullable();
            $table->string('admission_type')->default('regular'); // regular, transfer, direct
            $table->decimal('entrance_exam_score', 5, 2)->nullable();
            $table->string('photo')->nullable();
            $table->text('special_notes')->nullable();
            $table->json('social_media_links')->nullable();
            $table->string('identification_type')->nullable(); // passport, national_id, license
            $table->string('identification_number')->nullable();
            $table->timestamps();

            $table->index(['student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
