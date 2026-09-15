<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'subject_id',
        'teacher_id',
        'term_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'status',
        'term',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Named academicTerm (not term()) - this model has a legacy free-text
     * 'term' string column from Phase 12, before Terms existed as an entity.
     */
    public function academicTerm()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
