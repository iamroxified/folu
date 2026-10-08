<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenditures', function (Blueprint $table) {
            $table->id();
            $table->string('expenditure_number')->unique();
            $table->string('title');
            $table->string('category')->default('General');
            $table->decimal('amount', 12, 2);
            $table->date('expenditure_date');
            $table->string('vendor_recipient')->nullable();
            $table->string('payment_method')->default('cash');
            $table->enum('status', ['paid', 'pending', 'approved', 'cancelled'])->default('paid');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenditures');
    }
};
