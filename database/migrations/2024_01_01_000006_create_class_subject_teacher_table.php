<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assignment table: ties a teacher to a subject within a specific class.
        // This is what scopes a teacher's "my students" / "my marks upload" views,
        // and is also the basis for the teacher's timetable.
        Schema::create('class_subject_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_class_id', 'subject_id', 'teacher_id'], 'cst_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject_teacher');
    }
};
