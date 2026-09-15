<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An "exam" is a named assessment window (e.g. "Term 2 Mid-Term 2026", "Term 2 End-Term 2026")
        // scoped to a school and class. Marks are uploaded against an exam. The principal approves
        // the exam as a whole, which is what flips visibility for parents - not individual marks.
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('name'); // e.g. "Term 2 End-Term Exam 2026"
            $table->string('term')->nullable(); // e.g. "Term 2"
            $table->year('year')->nullable();
            $table->date('exam_date')->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
