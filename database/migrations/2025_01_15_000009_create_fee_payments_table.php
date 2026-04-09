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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique(); // Unique payment reference
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_fee_assignment_id');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'cheque', 'mobile_money', 'online'])->default('cash');
            $table->string('transaction_reference')->nullable(); // Bank/external transaction reference
            $table->date('payment_date');
            $table->enum('status', ['pending', 'confirmed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('payment_notes')->nullable();
            $table->json('payment_details')->nullable(); // Additional payment metadata
            $table->string('receipt_number')->nullable()->unique();
            $table->boolean('receipt_printed')->default(false);
            $table->timestamp('receipt_printed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'payment_date']);
            $table->index(['payment_date', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index(['received_by', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
