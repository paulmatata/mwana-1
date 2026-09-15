<?php

namespace App\Support;

use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;

class SubjectEnrollment
{
    /**
     * Enrolls a single student into every core subject currently assigned to
     * their class. Call this whenever a student is created, or moved into a
     * new class (edited, or promoted). Electives are deliberately untouched -
     * a teacher has to enroll students in those themselves.
     */
    public static function syncCoreSubjectsForStudent(Student $student): void
    {
        $coreSubjectIds = ClassSubjectTeacher::where('school_class_id', $student->school_class_id)
            ->whereHas('subject', fn ($q) => $q->where('is_core', true))
            ->pluck('subject_id')
            ->unique();

        if ($coreSubjectIds->isNotEmpty()) {
            $student->subjects()->syncWithoutDetaching($coreSubjectIds);
        }
    }

    /**
     * Enrolls every currently-active student in a class into a subject that
     * was just assigned to it, but only if the subject is core. Call this
     * whenever a new class-subject-teacher assignment is created.
     */
    public static function enrollClassIfCore(SchoolClass $class, Subject $subject): void
    {
        if (! $subject->is_core) {
            return;
        }

        $studentIds = $class->students()->where('status', 'active')->pluck('id');

        if ($studentIds->isNotEmpty()) {
            $subject->enrolledStudents()->syncWithoutDetaching($studentIds);
        }
    }
}
