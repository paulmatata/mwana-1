<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();

            $table->string('name');
            // Admission number is unique PER SCHOOL, not globally - two different schools
            // may both have an "ADM001". This pairing (school_id + admission_no) is exactly
            // what the parent first-login flow validates against, alongside the name.
            $table->string('admission_no');

            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('guardian_phone')->nullable(); // fallback contact even before a parent account is linked
            $table->enum('status', ['active', 'transferred', 'graduated', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['school_id', 'admission_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
