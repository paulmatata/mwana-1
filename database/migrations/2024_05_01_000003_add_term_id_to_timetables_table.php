<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('school_class_id')->constrained('terms')->nullOnDelete();
        });

        // Note: Phase 12's free-text 'term' column (e.g. "Term 2 2026 - Draft 1") isn't
        // parsed into real Term rows here - it was an unstructured label, not reliable
        // enough to auto-convert. Existing sessions stay unlinked (term_id null); new
        // sessions going forward pick a real Term from a dropdown instead of typing text.
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
