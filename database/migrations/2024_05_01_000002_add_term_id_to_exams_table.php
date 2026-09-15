<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('school_class_id')->constrained('terms')->nullOnDelete();
        });

        // Backfill: every distinct (school_id, term string, year) combination already
        // used by an exam becomes a real Term row, and every exam pointing at that
        // combination gets linked to it. Exams with no term/year text are left
        // unlinked (term_id stays null) rather than guessed at.
        $distinctCombos = DB::table('exams')
            ->whereNotNull('term')
            ->whereNotNull('year')
            ->select('school_id', 'term', 'year')
            ->distinct()
            ->get();

        foreach ($distinctCombos as $combo) {
            $termId = DB::table('terms')->where([
                'school_id' => $combo->school_id,
                'name' => $combo->term,
                'year' => $combo->year,
            ])->value('id');

            if (! $termId) {
                $termId = DB::table('terms')->insertGetId([
                    'school_id' => $combo->school_id,
                    'name' => $combo->term,
                    'year' => $combo->year,
                    'is_current' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('exams')
                ->where('school_id', $combo->school_id)
                ->where('term', $combo->term)
                ->where('year', $combo->year)
                ->update(['term_id' => $termId]);
        }
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
