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
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('guardian_id')->constrained()->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'guardian', 'uncle', 'aunt', 'grandfather', 'grandmother', 'other']);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('emergency_contact')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
            $table->index(['student_id']);
            $table->index(['guardian_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};
