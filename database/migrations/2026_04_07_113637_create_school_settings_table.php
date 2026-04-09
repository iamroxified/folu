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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Folu School Management System');
            $table->string('school_logo')->nullable(); // Path to logo file
            $table->text('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('school_email')->nullable();
            $table->text('school_motto')->nullable();
            $table->string('currency')->default('NGN');
            $table->string('timezone')->default('Africa/Lagos');
            $table->boolean('is_installed')->default(false);
            $table->json('additional_settings')->nullable(); // For extra configs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
