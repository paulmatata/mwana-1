<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Read-only from the parent's point of view - Mwana never processes a payment.
        // This table just reflects what the school (principal/bursar-role-via-principal, or
        // a teacher granted permission) has recorded: amount due, amount paid, balance, due date.
        Schema::create('fee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->string('term')->nullable(); // e.g. "Term 2"
            $table->year('year')->nullable();
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('due_date')->nullable(); // "when they will be sending fees home for"
            $table->text('notes')->nullable(); // e.g. breakdown, bank/paybill details for reference only
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_records');
    }
};
