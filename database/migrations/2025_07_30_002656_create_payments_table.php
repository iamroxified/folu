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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique(); // Unique payment reference number
            $table->enum('payable_type', ['student_fee', 'staff_salary']); // Polymorphic type
            $table->unsignedBigInteger('payable_id'); // Polymorphic ID
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'mobile_money', 'online'])->default('cash');
            $table->date('payment_date');
            $table->string('transaction_id')->nullable(); // For electronic payments
            $table->string('payer_name'); // Name of person making payment
            $table->string('payer_phone')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->json('payment_details')->nullable(); // Store additional payment info
            $table->timestamps();
            
            // Add indexes
            $table->index(['payable_type', 'payable_id']);
            $table->index('payment_date');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
