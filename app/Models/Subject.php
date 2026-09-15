<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'is_core',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * Students actually enrolled in this subject. For a core subject this is
     * effectively "everyone in the assigned class"; for an elective it's only
     * whoever the teacher deliberately enrolled.
     */
    public function enrolledStudents()
    {
        return $this->belongsToMany(Student::class, 'student_subject')->withTimestamps();
    }

    public function qualifiedTeachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id')->withTimestamps();
    }
}
