<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('entered_by')->constrained('users')->cascadeOnDelete(); // teacher who uploaded it

            $table->decimal('score', 5, 2)->nullable(); // e.g. out of 100
            $table->string('grade')->nullable(); // e.g. "A", "B+" - computed on save via GradingScale
            $table->text('remarks')->nullable();

            $table->timestamps();

            // one mark per student/subject/exam combination
            $table->unique(['exam_id', 'student_id', 'subject_id'], 'marks_unique_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
