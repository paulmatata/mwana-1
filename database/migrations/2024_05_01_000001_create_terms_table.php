<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A first-class "term" a school defines once (e.g. "Term 1" / 2026), which
        // exams, timetables, and fee transactions all tie back to via term_id. This
        // is what makes real history possible - "show me Term 2 2026" now means an
        // actual query, not hoping a free-text label was typed consistently.
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Term 1", "Term 2", "Term 3"
            $table->year('year');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // Only one term per school should be "current" at a time - used as the
            // sensible default when creating a new exam, fee charge, or timetable slot.
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'name', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
