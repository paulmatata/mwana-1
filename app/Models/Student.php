<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'name',
        'admission_no',
        'gender',
        'date_of_birth',
        'guardian_phone',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot('linked_at')
            ->withTimestamps();
    }

    /**
     * Subjects this student is actually enrolled in - the source of truth for
     * what marks are ever expected for them. See Subject::enrolledStudents()
     * for the inverse and the core/elective auto-enrollment logic.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_subject')->withTimestamps();
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function feeTransactions()
    {
        return $this->hasMany(FeeTransaction::class)->orderBy('transaction_date')->orderBy('id');
    }

    /**
     * The student's current fee balance - simply the running balance on their
     * most recent transaction. Zero if they have no fee history at all.
     */
    public function currentFeeBalance(): float
    {
        return (float) ($this->feeTransactions()->latest('transaction_date')->latest('id')->value('running_balance') ?? 0);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    /**
     * Only marks belonging to an approved exam are visible to parents.
     */
    public function approvedMarks()
    {
        return $this->marks()->whereHas('exam', fn ($q) => $q->where('status', 'approved'));
    }
}
