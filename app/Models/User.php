<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role',
        'name',
        'email',
        'phone',
        'password',
        'school_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relationships ---

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function studentsAsParent()
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('linked_at')
            ->withTimestamps();
    }

    public function classTeacherOf()
    {
        return $this->hasMany(SchoolClass::class, 'class_teacher_id');
    }

    public function subjectAssignments()
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'teacher_id');
    }

    /**
     * Subjects this teacher is qualified to teach at their school. Empty means
     * "no qualifications configured yet" - treated permissively (assignable to
     * anything) rather than as a hard lock, see Principal\AssignmentController.
     */
    public function subjectsQualifiedFor()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')->withTimestamps();
    }

    public function timetableSlots()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    // --- Role helpers ---

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPrincipal(): bool
    {
        return $this->role === 'principal';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->role === 'parent';
    }
}
