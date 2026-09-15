<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces fee_records (one mutable row per term, easy to lose history in)
     * with a real ledger: every charge and every payment is its own row, and each
     * row stores the running balance immediately after it's applied - the same
     * pattern a bank statement uses. A student's current balance is just their
     * most recent transaction's running_balance, and their full payment history
     * is simply every row in order, nothing ever gets overwritten.
     */
    public function up(): void
    {
        Schema::create('fee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();

            $table->enum('type', ['debit', 'credit']); // debit = a charge billed; credit = a payment recorded
            $table->decimal('amount', 10, 2);
            $table->decimal('running_balance', 10, 2); // balance for this student immediately after this transaction
            $table->string('description'); // e.g. "Term 2 2026 tuition", "Payment received - M-Pesa"
            $table->date('transaction_date'); // when it happened, not necessarily when it was entered into Mwana
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['student_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_transactions');
    }
};
