<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id',
        'name',
        'stream',
        'class_teacher_id',
        'promotes_to_class_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    /**
     * The class this class's students move into at year-end promotion.
     * Null means this is a terminal/graduating class.
     */
    public function promotesTo()
    {
        return $this->belongsTo(SchoolClass::class, 'promotes_to_class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subjectAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function timetableSlots()
    {
        return $this->hasMany(Timetable::class);
    }

    public function displayName(): string
    {
        return $this->stream ? "{$this->name} {$this->stream}" : $this->name;
    }
}
