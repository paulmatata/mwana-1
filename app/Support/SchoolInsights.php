<?php

namespace App\Support;

use App\Models\ClassSubjectTeacher;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\Subject;

/**
 * Surfaces things a principal or class teacher should probably know about,
 * without them having to go looking - missing class teachers, subjects with
 * nobody assigned to teach them, underperforming classes/subjects, and
 * students who seem to be missing exams. Computed fresh on each dashboard
 * load rather than by a scheduled job: at the size of one school's data this
 * is cheap, and it means the numbers are always current. If this ever needs
 * to run across many schools at once on a schedule instead (e.g. a daily
 * digest), the same queries here are the starting point for that job.
 */
class SchoolInsights
{
    protected const LOW_SCORE_THRESHOLD = 50;
    protected const UNDERPERFORM_GAP = 15; // points below the subject's school-wide average

    /**
     * School-wide insights, for the principal's dashboard.
     */
    public static function forSchool(int $schoolId): array
    {
        $insights = [];

        $classes = SchoolClass::where('school_id', $schoolId)->get();

        self::classesWithoutClassTeacher($classes, $insights);
        self::classesWithNoAssignments($classes, $insights);
        self::coreSubjectCoverageGaps($schoolId, $classes, $insights);
        self::underperformingClasses($classes, $insights);
        self::studentsMissingExams($classes, $insights);
        self::underperformingSubjectTeachers($schoolId, $insights);

        return $insights;
    }

    /**
     * Scoped to a single class, for a class teacher's dashboard.
     */
    public static function forClass(SchoolClass $class): array
    {
        $insights = [];
        $classes = collect([$class]);

        self::classesWithNoAssignments($classes, $insights);
        self::underperformingClasses($classes, $insights);
        self::studentsMissingExams($classes, $insights);

        return $insights;
    }

    protected static function classesWithoutClassTeacher($classes, array &$insights): void
    {
        foreach ($classes->whereNull('class_teacher_id') as $class) {
            $insights[] = [
                'level' => 'warning',
                'message' => "{$class->displayName()} has no class teacher assigned - nobody can submit its exam results for approval.",
            ];
        }
    }

    protected static function classesWithNoAssignments($classes, array &$insights): void
    {
        foreach ($classes as $class) {
            if (! ClassSubjectTeacher::where('school_class_id', $class->id)->exists()) {
                $insights[] = [
                    'level' => 'danger',
                    'message' => "{$class->displayName()} has no subjects assigned to any teacher yet.",
                ];
            }
        }
    }

    protected static function coreSubjectCoverageGaps(int $schoolId, $classes, array &$insights): void
    {
        $coreSubjects = Subject::where('school_id', $schoolId)->where('is_core', true)->get();

        foreach ($coreSubjects as $subject) {
            $classIdsWithAssignment = ClassSubjectTeacher::where('subject_id', $subject->id)->pluck('school_class_id');

            // Only flag a gap if the subject IS taught somewhere in the school -
            // otherwise this would just be noise for every unused subject.
            if ($classIdsWithAssignment->isEmpty()) {
                continue;
            }

            $classesMissing = $classes->whereNotIn('id', $classIdsWithAssignment);

            foreach ($classesMissing as $class) {
                $insights[] = [
                    'level' => 'warning',
                    'message' => "{$subject->name} has no teacher assigned in {$class->displayName()}, though it's taught elsewhere in the school.",
                ];
            }
        }
    }

    protected static function underperformingClasses($classes, array &$insights): void
    {
        foreach ($classes as $class) {
            $latestExam = Exam::where('school_class_id', $class->id)
                ->where('status', 'approved')
                ->latest('approved_at')
                ->first();

            if (! $latestExam) {
                continue;
            }

            $average = $latestExam->marks()->avg('score');

            if ($average !== null && $average < self::LOW_SCORE_THRESHOLD) {
                $insights[] = [
                    'level' => 'danger',
                    'message' => "{$class->displayName()}'s {$latestExam->name} average is ".round($average, 1).' - below '.self::LOW_SCORE_THRESHOLD.'.',
                ];
            }
        }
    }

    protected static function studentsMissingExams($classes, array &$insights): void
    {
        foreach ($classes as $class) {
            $latestExam = Exam::where('school_class_id', $class->id)
                ->where('status', 'approved')
                ->latest('approved_at')
                ->first();

            if (! $latestExam) {
                continue;
            }

            $studentsWithMarks = $latestExam->marks()->pluck('student_id')->unique();
            $activeStudents = $class->students()->where('status', 'active')->get(['id', 'name']);
            $missing = $activeStudents->whereNotIn('id', $studentsWithMarks);

            if ($missing->isNotEmpty()) {
                $names = $missing->pluck('name')->take(5)->implode(', ');
                $more = $missing->count() > 5 ? ' and '.($missing->count() - 5).' more' : '';

                $insights[] = [
                    'level' => 'warning',
                    'message' => "{$missing->count()} student(s) in {$class->displayName()} have no marks recorded for {$latestExam->name}: {$names}{$more}.",
                ];
            }
        }
    }

    /**
     * Flags a subject-teacher-class combination whose latest-exam average is
     * notably below that same subject's school-wide average - a signal that
     * either the teaching, or something else about that class, needs attention.
     */
    protected static function underperformingSubjectTeachers(int $schoolId, array &$insights): void
    {
        $subjects = Subject::where('school_id', $schoolId)->get();

        foreach ($subjects as $subject) {
            $schoolWideAverage = Mark::where('subject_id', $subject->id)
                ->whereHas('exam', fn ($q) => $q->where('status', 'approved'))
                ->avg('score');

            if ($schoolWideAverage === null) {
                continue;
            }

            $assignments = ClassSubjectTeacher::where('subject_id', $subject->id)->with(['schoolClass', 'teacher'])->get();

            foreach ($assignments as $assignment) {
                $latestExam = Exam::where('school_class_id', $assignment->school_class_id)
                    ->where('status', 'approved')
                    ->latest('approved_at')
                    ->first();

                if (! $latestExam) {
                    continue;
                }

                $classSubjectAverage = Mark::where('exam_id', $latestExam->id)
                    ->where('subject_id', $subject->id)
                    ->avg('score');

                if ($classSubjectAverage !== null && ($schoolWideAverage - $classSubjectAverage) >= self::UNDERPERFORM_GAP) {
                    $insights[] = [
                        'level' => 'warning',
                        'message' => "{$subject->name} in {$assignment->schoolClass->displayName()} (taught by {$assignment->teacher->name}) averaged ".round($classSubjectAverage, 1)." in {$latestExam->name} - well below the school-wide {$subject->name} average of ".round($schoolWideAverage, 1).'.',
                    ];
                }
            }
        }
    }
}
