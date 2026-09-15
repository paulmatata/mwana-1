<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Every student and class-subject-teacher assignment created before this
     * phase has zero rows in student_subject, since the table didn't exist yet.
     * Without this backfill, every already-set-up class would suddenly show an
     * empty marks-upload roster for every core subject the moment this migrates -
     * a silent breakage for schools already using the system. This enrolls every
     * active student into every core subject already assigned to their class.
     */
    public function up(): void
    {
        $rows = DB::table('class_subject_teacher as cst')
            ->join('subjects as sub', 'sub.id', '=', 'cst.subject_id')
            ->join('students as s', function ($join) {
                $join->on('s.school_class_id', '=', 'cst.school_class_id')
                    ->where('s.status', '=', 'active');
            })
            ->where('sub.is_core', true)
            ->select('s.id as student_id', 'sub.id as subject_id')
            ->distinct()
            ->get();

        $now = now();

        $rows->chunk(500)->each(function ($chunk) use ($now) {
            $insert = $chunk->map(fn ($row) => [
                'student_id' => $row->student_id,
                'subject_id' => $row->subject_id,
                'enrolled_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            DB::table('student_subject')->insertOrIgnore($insert);
        });
    }

    public function down(): void
    {
        // Not reversible in a meaningful way - the enrollment rows this created
        // are indistinguishable from ones created normally afterward.
    }
};
