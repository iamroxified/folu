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
        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('resource'); // e.g., 'students', 'staff', 'payroll', etc.
            $table->json('permissions'); // e.g., ['create', 'read', 'update', 'delete']
            $table->timestamps();
            
            // Unique constraint to prevent duplicate permissions for same role and resource
            $table->unique(['role_id', 'resource']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorizations');
    }
};
