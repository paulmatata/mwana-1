<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Named "school_classes" (not "classes") to avoid clashing with PHP's reserved word
        // and to keep it unambiguous alongside "Subjects" and "Users".
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Grade 5", "Form 2"
            $table->string('stream')->nullable(); // e.g. "Blue", "East"
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'name', 'stream']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
