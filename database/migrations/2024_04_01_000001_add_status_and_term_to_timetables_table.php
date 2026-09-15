<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            // A newly-built slot starts as 'draft' so a principal can build out a whole
            // class's week without teachers seeing a half-finished schedule. Publishing
            // a class (see PromotionController-style "review then commit" pattern used
            // elsewhere) flips its slots to 'final', which is the only status a teacher's
            // own timetable view ever shows.
            $table->enum('status', ['draft', 'final'])->default('draft')->after('room');

            // Free-text label so a principal can tell drafts apart while building
            // (e.g. "Term 2 2026", "Draft 2"). Purely informational - it doesn't gate
            // visibility on its own, only 'status' does.
            $table->string('term')->nullable()->after('status');
        });

        // Every row that exists at the moment this migration runs was created before
        // Phase 12 existed, meaning it was already being shown on teacher timetables.
        // Defaulting the new column to 'draft' would silently hide all of it - so mark
        // everything already here as 'final' right away. Anything created after this
        // point goes through the app, which explicitly sets 'draft' on new slots.
        DB::table('timetables')->update(['status' => 'final']);
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn(['status', 'term']);
        });
    }
};
